<?php
/**
 * beanstalk: A minimalistic PHP beanstalk client.
 *
 * Copyright (c) 2009-2015 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Codeages\Beanstalk;

use RuntimeException;
use Codeages\Beanstalk\Exception\SocketException;
use Codeages\Beanstalk\Exception\ConnectionException;
use Codeages\Beanstalk\Exception\ServerException;
use Codeages\Beanstalk\Exception\DeadlineSoonException;

/**
 * An interface to the beanstalk queue service. Implements the beanstalk
 * protocol spec 1.9. Where appropriate the documentation from the protocol
 * has been added to the docblocks in this class.
 *
 * @link https://github.com/kr/beanstalkd/blob/master/doc/protocol.txt
 */
class Client
{
    /**
     * Minimum priority value which can be assigned to a job. The minimum
     * priority value is also the _highest priority_ a job can have.
     *
     * @var int
     */
    const MIN_PRIORITY = 0;

    /**
     * Maximum priority value which can be assigned to a job. The maximum
     * priority value is also the _lowest priority_ a job can have.
     *
     * @var int
     */
    const MAX_PRIORITY = 4294967295;

    /**
     * Holds a boolean indicating whether a connection to the server is
     * currently established or not.
     *
     * @var bool
     */
    public $connected = false;

    /**
     * Holds configuration values.
     *
     * @var array
     */
    protected $_config = [];

    /**
     * The current connection resource handle (if any).
     *
     * @var resource
     */
    protected $_connection;

    protected $_usingTube = null;

    protected $_watchingTubes = [];

    /**
     * Constructor.
     *
     * @param array $config An array of configuration values:
     *                      - `'persistent'`  Whether to make the connection persistent or
     *                      not, defaults to `true` as the FAQ recommends
     *                      persistent connections.
     *                      - `'host'`        The beanstalk server hostname or IP address to
     *                      connect to, defaults to `127.0.0.1`.
     *                      - `'port'`        The port of the server to connect to, defaults
     *                      to `11300`.
     *                      - `'timeout'`     Timeout in seconds when establishing the
     *                      connection, defaults to `1`.
     *                      - `'socket_timeout'` Socket timeout, defaults to `20` seconds.
     *                      - `'logger'`      An instance of a PSR-3 compatible logger.
     *
     * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'persistent' => true,
            'host' => '127.0.0.1',
            'port' => 11300,
            'timeout' => 2,
            'socket_timeout' => 20,
            'logger' => null,
        ];
        $this->_config = $config + $defaults;
    }

    /**
     * Destructor, disconnects from the server.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Initiates a socket connection to the beanstalk server. The resulting
     * stream will not have any timeout set on it. Which means it can wait
     * an unlimited amount of time until a packet becomes available. This
     * is required for doing blocking reads.
     *
     * @see \Beanstalk\Client::$_connection
     * @see \Beanstalk\Client::reserve()
     *
     * @return bool `true` if the connection was established, `false` otherwise.
     */
    public function connect()
    {
        if (isset($this->_connection)) {
            $this->disconnect();
        }

        $function = $this->_config['persistent'] ? 'pfsockopen' : 'fsockopen';
        $params = [$this->_config['host'], $this->_config['port'], &$errNum, &$errStr];

        if ($this->_config['timeout']) {
            $params[] = $this->_config['timeout'];
        }
        $this->_connection = @call_user_func_array($function, $params);

        if (!empty($errNum) || !empty($errStr)) {
            throw new ConnectionException("{$errNum}: {$errStr} (connecting to {$this->_config['host']}:{$this->_config['port']})");
        }

        $this->connected = is_resource($this->_connection);
        if (!$this->connected) {
            throw new ConnectionException('Connected failed.');
        }

        stream_set_timeout($this->_connection, $this->_config['socket_timeout']);

        return $this->connected;
    }

    /**
     * Closes the connection to the beanstalk server by first signaling
     * that we want to quit then actually closing the socket connection.
     *
     * @return bool `true` if diconnecting was successful.
     */
    public function disconnect()
    {
        if (!is_resource($this->_connection)) {
            $this->connected = false;
        } else {
            $this->_write('quit');
            $this->connected = !fclose($this->_connection);

            if (!$this->connected) {
                $this->_connection = null;
            }
        }

        return !$this->connected;
    }

    /**
     * Writes a packet to the socket. Prior to writing to the socket will
     * check for availability of the connection.
     *
     * @param string $data
     *
     * @return int|bool number of written bytes or `false` on error.
     */
    protected function _write($data)
    {
        if (!$this->connected) {
            $message = 'No connecting found while writing data to socket.';
            throw new RuntimeException($message);
        }

        $data .= "\r\n";

        $length = strlen($data);
        $fwrited = fwrite($this->_connection, $data, $length);

        if ($fwrited === false) {
            throw new SocketException('Socket is broken, write failed: '.substr($data, 0, 100));
        } elseif ($fwrited < $length) {
            throw new SocketException("Socket is broken, only write {$fwrited} bytes of {$length} bytes: ".substr($data, 0, 100));
        }

        return $fwrited;
    }

    public function reconnect()
    {
        @fclose($this->_connection);
        $this->_connection = null;
        $this->connected = false;
        $this->connect();

        if ($this->_usingTube) {
            $this->useTube($this->_usingTube);
        }

        foreach ($this->_watchingTubes as $tube => $true) {
            $this->watch($tube);
        }
    }

    /**
     * Reads a packet from the socket. Prior to reading from the socket
     * will check for availability of the connection.
     *
     * @param int $length Number of bytes to read.
     *
     * @return string|bool Data or `false` on error.
     */
    protected function _read($length = null)
    {
        if (!$this->connected) {
            $message = 'No connection found while reading data from socket.';
            throw new RuntimeException($message);
        }
        if ($length) {
            if (feof($this->_connection)) {
                throw new SocketException('Connection is closed by server.');
            }
            $data = stream_get_contents($this->_connection, $length + 2);
            if ($data === false) {
                throw new SocketException('Read content from connection error.');
            }

            $meta = stream_get_meta_data($this->_connection);
            if ($meta['timed_out']) {
                throw new SocketException('Connection timed out while reading data from socket.');
            }

            $packet = rtrim($data, "\r\n");
        } else {
            $packet = stream_get_line($this->_connection, 16384, "\r\n");
            if ($packet === false) {
                throw new SocketException('Read content from connection error.');
            }
        }

        return $packet;
    }

    /* Producer Commands */

    /**
     * The `put` command is for any process that wants to insert a job into the queue.
     *
     * @param int    $pri   Jobs with smaller priority values will be scheduled
     *                      before jobs with larger priorities. The most urgent priority is
     *                      0; the least urgent priority is 4294967295.
     * @param int    $delay Seconds to wait before putting the job in the
     *                      ready queue.  The job will be in the "delayed" state during this time.
     * @param int    $ttr   Time to run - Number of seconds to allow a worker to
     *                      run this job.  The minimum ttr is 1.
     * @param string $data  The job body.
     *
     * @return int|bool `false` on error otherwise an integer indicating
     *                  the job id.
     */
    public function put($pri, $delay, $ttr, $data)
    {
        $this->_write(sprintf("put %d %d %d %d\r\n%s", $pri, $delay, $ttr, strlen($data), $data));
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'INSERTED':
            case 'BURIED':
                return (integer) strtok(' '); // job id
            case 'EXPECTED_CRLF':
            case 'JOB_TOO_BIG':
            default:
                throw new ServerException('Put error: '.$status);
        }
    }

    /**
     * The `use` command is for producers. Subsequent put commands will put
     * jobs into the tube specified by this command. If no use command has
     * been issued, jobs will be put into the tube named `default`.
     *
     * @param string $tube A name at most 200 bytes. It specifies the tube to
     *                     use. If the tube does not exist, it will be created.
     *
     * @return string|bool `false` on error otherwise the name of the tube.
     */
    public function useTube($tube)
    {
        $this->_usingTube = $tube;
        $this->_write(sprintf('use %s', $tube));
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'USING':
                return strtok(' ');
            default:
                throw new ServerException('UseTube error: '.$status);
        }
    }

    /**
     * Pause a tube delaying any new job in it being reserved for a given time.
     *
     * @param string $tube  The name of the tube to pause.
     * @param int    $delay Number of seconds to wait before reserving any more
     *                      jobs from the queue.
     *
     * @return bool `false` on error otherwise `true`.
     */
    public function pauseTube($tube, $delay)
    {
        $this->_write(sprintf('pause-tube %s %d', $tube, $delay));
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'PAUSED':
                return true;
            case 'NOT_FOUND':
                return false;
            default:
                throw new ServerException('PauseTube error: '.$status);
        }
    }

    /* Worker Commands */

    /**
     * Reserve a job (with a timeout).
     *
     * @param int $timeout If given specifies number of seconds to wait for
     *                     a job. `0` returns immediately.
     *
     * @return array|false `false` on error otherwise an array holding job id
     *                     and body.
     */
    public function reserve($timeout = null)
    {
        if (isset($timeout)) {
            $this->_write(sprintf('reserve-with-timeout %d', $timeout));
        } else {
            $this->_write('reserve');
        }
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'RESERVED':
                return [
                    'id' => (integer) strtok(' '),
                    'body' => $this->_read((integer) strtok(' ')),
                ];
            case 'TIMED_OUT':
                return false;
            case 'DEADLINE_SOON':
                throw new DeadlineSoonException('Reserve failed: '.$status);
            default:
                throw new ServerException('Reserve failed: '.$status);
        }
    }

    /**
     * Removes a job from the server entirely.
     *
     * @param int $id The id of the job.
     *
     * @return bool `false` on error, `true` on success.
     */
    public function delete($id)
    {
        $this->_write(sprintf('delete %d', $id));
        $status = $this->_read();

        switch ($status) {
            case 'DELETED':
                return true;
            case 'NOT_FOUND':
                return false;
            default:
                throw new ServerException('Delete error: '.$status);
        }
    }

    /**
     * Puts a reserved job back into the ready queue.
     *
     * @param int $id    The id of the job.
     * @param int $pri   Priority to assign to the job.
     * @param int $delay Number of seconds to wait before putting the job in the ready queue.
     *
     * @return bool `false` on error, `true` on success.
     */
    public function release($id, $pri, $delay)
    {
        $this->_write(sprintf('release %d %d %d', $id, $pri, $delay));
        $status = $this->_read();

        switch ($status) {
            case 'RELEASED':
            case 'BURIED':
                return true;
            case 'NOT_FOUND':
                return false;
            default:
                throw new ServerException('Release error: '.$status);
        }
    }

    /**
     * Puts a job into the `buried` state Buried jobs are put into a FIFO
     * linked list and will not be touched until a client kicks them.
     *
     * @param int $id  The id of the job.
     * @param int $pri *New* priority to assign to the job.
     *
     * @return bool `false` on error, `true` on success.
     */
    public function bury($id, $pri)
    {
        $this->_write(sprintf('bury %d %d', $id, $pri));
        $status = $this->_read();

        switch ($status) {
            case 'BURIED':
                return true;
            case 'NOT_FOUND':
                return false;
            default:
                throw new ServerException('Bury error: '.$status);
        }
    }

    /**
     * Allows a worker to request more time to work on a job.
     *
     * @param int $id The id of the job.
     *
     * @return bool `false` on error, `true` on success.
     */
    public function touch($id)
    {
        $this->_write(sprintf('touch %d', $id));
        $status = $this->_read();

        switch ($status) {
            case 'TOUCHED':
                return true;
            case 'NOT_TOUCHED':
                return false;
            default:
                throw new ServerException('Touch error: '.$status);
        }
    }

    /**
     * Adds the named tube to the watch list for the current connection.
     *
     * @param string $tube Name of tube to watch.
     *
     * @return int|bool `false` on error otherwise number of tubes in watch list.
     */
    public function watch($tube)
    {
        $this->_watchingTubes[$tube] = true;
        $this->_write(sprintf('watch %s', $tube));
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'WATCHING':
                return (integer) strtok(' ');
            default:
                throw new ServerException('Watch error: '.$status);
        }
    }

    /**
     * Remove the named tube from the watch list.
     *
     * @param string $tube Name of tube to ignore.
     *
     * @return int|bool `false` on error otherwise number of tubes in watch list.
     */
    public function ignore($tube)
    {
        $this->_write(sprintf('ignore %s', $tube));
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'WATCHING':
                return (integer) strtok(' ');
            case 'NOT_IGNORED':
                return false;
            default:
                throw new ServerException('Ignore error: '.$status);
        }
    }

    /* Other Commands */

    /**
     * Inspect a job by its id.
     *
     * @param int $id The id of the job.
     *
     * @return string|bool `false` on error otherwise the body of the job.
     */
    public function peek($id)
    {
        $this->_write(sprintf('peek %d', $id));

        return $this->_peekRead();
    }

    /**
     * Inspect the next ready job.
     *
     * @return string|bool `false` on error otherwise the body of the job.
     */
    public function peekReady()
    {
        $this->_write('peek-ready');

        return $this->_peekRead();
    }

    /**
     * Inspect the job with the shortest delay left.
     *
     * @return string|bool `false` on error otherwise the body of the job.
     */
    public function peekDelayed()
    {
        $this->_write('peek-delayed');

        return $this->_peekRead();
    }

    /**
     * Inspect the next job in the list of buried jobs.
     *
     * @return string|bool `false` on error otherwise the body of the job.
     */
    public function peekBuried()
    {
        $this->_write('peek-buried');

        return $this->_peekRead();
    }

    /**
     * Handles response for all peek methods.
     *
     * @return string|bool `false` on error otherwise the body of the job.
     */
    protected function _peekRead()
    {
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'FOUND':
                return [
                    'id' => (integer) strtok(' '),
                    'body' => $this->_read((integer) strtok(' ')),
                ];
            case 'NOT_FOUND':
                return false;
            default:
                throw new ServerException('Peek error: '.$status);
        }
    }

    /**
     * Moves jobs into the ready queue (applies to the current tube).
     *
     * If there are buried jobs those get kicked only otherwise delayed
     * jobs get kicked.
     *
     * @param int $bound Upper bound on the number of jobs to kick.
     *
     * @return int|bool False on error otherwise number of jobs kicked.
     */
    public function kick($bound)
    {
        $this->_write(sprintf('kick %d', $bound));
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'KICKED':
                return (integer) strtok(' ');
            default:
                throw new ServerException('Kick error: '.$status);
        }
    }

    /**
     * This is a variant of the kick command that operates with a single
     * job identified by its job id. If the given job id exists and is in a
     * buried or delayed state, it will be moved to the ready queue of the
     * the same tube where it currently belongs.
     *
     * @param int $id The job id.
     *
     * @return bool `false` on error `true` otherwise.
     */
    public function kickJob($id)
    {
        $this->_write(sprintf('kick-job %d', $id));
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'KICKED':
                return true;
            case 'NOT_FOUND':
                return false;
            default:
                throw new ServerException('Kick error: '.$status);
        }
    }

    /* Stats Commands */

    /**
     * Gives statistical information about the specified job if it exists.
     *
     * @param int $id The job id.
     *
     * @return string|bool `false` on error otherwise a string with a yaml formatted dictionary.
     */
    public function statsJob($id)
    {
        $this->_write(sprintf('stats-job %d', $id));

        return $this->_statsRead();
    }

    /**
     * Gives statistical information about the specified tube if it exists.
     *
     * @param string $tube Name of the tube.
     *
     * @return string|bool `false` on error otherwise a string with a yaml formatted dictionary.
     */
    public function statsTube($tube)
    {
        $this->_write(sprintf('stats-tube %s', $tube));

        return $this->_statsRead();
    }

    /**
     * Gives statistical information about the system as a whole.
     *
     * @return string|bool `false` on error otherwise a string with a yaml formatted dictionary.
     */
    public function stats()
    {
        $this->_write('stats');

        return $this->_statsRead();
    }

    /**
     * Returns a list of all existing tubes.
     *
     * @return string|bool `false` on error otherwise a string with a yaml formatted list.
     */
    public function listTubes()
    {
        $this->_write('list-tubes');

        return $this->_statsRead();
    }

    /**
     * Returns the tube currently being used by the producer.
     *
     * @return string|bool `false` on error otherwise a string with the name of the tube.
     */
    public function listTubeUsed()
    {
        $this->_write('list-tube-used');
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'USING':
                return strtok(' ');
            default:
                throw new ServerException('List tube used error: '.$status);
        }
    }

    /**
     * Returns a list of tubes currently being watched by the worker.
     *
     * @return string|bool `false` on error otherwise a string with a yaml formatted list.
     */
    public function listTubesWatched()
    {
        $this->_write('list-tubes-watched');

        return $this->_statsRead();
    }

    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Handles responses for all stat methods.
     *
     * @param bool $decode Whether to decode data before returning it or not. Default is `true`.
     *
     * @return array|string|bool `false` on error otherwise statistical data.
     */
    protected function _statsRead($decode = true)
    {
        $status = strtok($this->_read(), ' ');

        switch ($status) {
            case 'OK':
                $data = $this->_read((integer) strtok(' '));

                return $decode ? $this->_decode($data) : $data;
            default:
                throw new ServerException('Stats error: '.$status);
        }
    }

    /**
     * Decodes YAML data. This is a super naive decoder which just works on
     * a subset of YAML which is commonly returned by beanstalk.
     *
     * @param string $data The data in YAML format, can be either a list or a dictionary.
     *
     * @return array An (associative) array of the converted data.
     */
    protected function _decode($data)
    {
        $data = array_slice(explode("\n", $data), 1);
        $result = [];

        foreach ($data as $key => $value) {
            if ($value[0] === '-') {
                $value = ltrim($value, '- ');
            } elseif (strpos($value, ':') !== false) {
                list($key, $value) = explode(':', $value);
                $value = ltrim($value, ' ');
            }
            if (is_numeric($value)) {
                $value = (integer) $value == $value ? (integer) $value : (float) $value;
            }
            $result[$key] = $value;
        }

        return $result;
    }
}
