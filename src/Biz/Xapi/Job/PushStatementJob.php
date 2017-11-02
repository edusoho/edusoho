<?php

namespace Biz\Xapi\Job;

use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use AppBundle\Common\ArrayToolkit;
use Guzzle\Http\Client;

class PushStatementsJob extends AbstractJob
{

    public function execute()
    {
        $condition = array(
            'status' => 'created'
        );
        $statements = $this->getXapiService()->searchStatements($condition, array('created_time' => 'ASC'), 0, 100);
        $statementIds = ArrayToolkit::column($statements, 'id');

        $this->getXapiService()->updateStatementsPushingByStatementIds($statementIds);
        $result = $this->pushStatements($statements);
        if ($result) {
            $this->getXapiService()->updateStatementsPushedByStatementIds($statementIds);
        }
    }

    protected function pushStatements($statements)
    {
        $pushdStatements = array();
        foreach ($statements as $statement) {
            $pushStatement = ArrayToolkit::parts($statement['data'], array('actor', 'verb', 'object', 'result', 'context'));
            $pushStatement['timestamp'] = time();
            $pushStatement['id'] = $statement['uuid'];
            $pushdStatements[] = $pushStatement;
        }

        $client = new Client();
        $request = $client->post($this->biz['xapi.options']['getway'], array(
            'Content-type' => 'application/json; charset=utf-8',
        ), json_encode($pushdStatements));

        $response = $request->send();
        if ($response->getStatusCode() == 200) {
            return true;
        }

        return false;
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }
}
