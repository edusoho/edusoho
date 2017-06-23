<?php


namespace Stecman\Component\Symfony\Console\BashCompletion;

/**
 * Command line context for completion
 *
 * Represents the current state of the command line that is being completed
 */
class CompletionContext
{
    /**
     * The current contents of the command line as a single string
     *
     * Bash equivalent: COMP_LINE
     *
     * @var string
     */
    protected $commandLine;

    /**
     * The index of the user's cursor relative to the start of the command line.
     *
     * If the current cursor position is at the end of the current command,
     * the value of this variable is equal to the length of $this->commandLine
     *
     * Bash equivalent: COMP_POINT
     *
     * @var int
     */
    protected $charIndex = 0;

    /**
     * An array containing the individual words in the current command line.
     *
     * This is not set until $this->splitCommand() is called, when it is populated by
     * $commandLine exploded by $wordBreaks
     *
     * Bash equivalent: COMP_WORDS
     *
     * @var array|null
     */
    protected $words = null;

    /**
     * The index in $this->words containing the word at the current cursor position.
     *
     * This is not set until $this->splitCommand() is called.
     *
     * Bash equivalent: COMP_CWORD
     *
     * @var int|null
     */
    protected $wordIndex = null;

    /**
     * Characters that $this->commandLine should be split on to get a list of individual words
     *
     * Bash equivalent: COMP_WORDBREAKS
     *
     * @var string
     */
    protected $wordBreaks = "'\"()= \t\n";

    /**
     * Set the whole contents of the command line as a string
     *
     * @param string $commandLine
     */
    public function setCommandLine($commandLine)
    {
        $this->commandLine = $commandLine;
        $this->reset();
    }

    /**
     * Return the current command line verbatim as a string
     *
     * @return string
     */
    public function getCommandLine()
    {
        return $this->commandLine;
    }

    /**
     * Return the word from the command line that the cursor is currently in
     *
     * Most of the time this will be a partial word. If the cursor has a space before it,
     * this will return an empty string, indicating a new word.
     *
     * @return string
     */
    public function getCurrentWord()
    {
        if (isset($this->words[$this->wordIndex])) {
            return $this->words[$this->wordIndex];
        }

        return '';
    }

    /**
     * Return a word by index from the command line
     *
     * @see $words, $wordBreaks
     * @param int $index
     * @return string
     */
    public function getWordAtIndex($index)
    {
        if (isset($this->words[$index])) {
            return $this->words[$index];
        }

        return '';
    }

    /**
     * Get the contents of the command line, exploded into words based on the configured word break characters
     *
     * @see $wordBreaks, setWordBreaks
     * @return array
     */
    public function getWords()
    {
        if ($this->words === null) {
            $this->splitCommand();
        }

        return $this->words;
    }

    /**
     * Get the index of the word the cursor is currently in
     *
     * @see getWords, getCurrentWord
     * @return int
     */
    public function getWordIndex()
    {
        if ($this->wordIndex === null) {
            $this->splitCommand();
        }

        return $this->wordIndex;
    }

    /**
     * Get the character index of the user's cursor on the command line
     *
     * This is in the context of the full command line string, so includes word break characters.
     * Note that some shells can only provide an approximation for character index. Under ZSH for
     * example, this will always be the character at the start of the current word.
     *
     * @return int
     */
    public function getCharIndex()
    {
        return $this->charIndex;
    }

    /**
     * Set the cursor position as a character index relative to the start of the command line
     *
     * @param int $index
     */
    public function setCharIndex($index)
    {
        $this->charIndex = $index;
        $this->reset();
    }

    /**
     * Set characters to use as split points when breaking the command line into words
     *
     * This defaults to a sane value based on BASH's word break characters and shouldn't
     * need to be changed unless your completions contain the default word break characters.
     *
     * @see wordBreaks
     * @param string $charList - a single string containing all of the characters to break words on
     */
    public function setWordBreaks($charList)
    {
        $this->wordBreaks = $charList;
        $this->reset();
    }

    /**
     * Split the command line into words using the configured word break characters
     *
     * @return string[]
     */
    protected function splitCommand()
    {
        $this->words = array();
        $this->wordIndex = null;
        $cursor = 0;

        $breaks = preg_quote($this->wordBreaks);

        if (!preg_match_all("/([^$breaks]*)([$breaks]*)/", $this->commandLine, $matches)) {
            return;
        }

        // Groups:
        // 1: Word
        // 2: Break characters
        foreach ($matches[0] as $index => $wholeMatch) {
            // Determine which word the cursor is in
            $cursor += strlen($wholeMatch);
            $word = $matches[1][$index];
            $breaks = $matches[2][$index];

            if ($this->wordIndex === null && $cursor >= $this->charIndex) {
                $this->wordIndex = $index;

                // Find the user's cursor position relative to the end of this word
                // The end of the word is the internal cursor minus any break characters that were captured
                $cursorWordOffset = $this->charIndex - ($cursor - strlen($breaks));

                if ($cursorWordOffset < 0) {
                    // Cursor is inside the word - truncate the word at the cursor
                    // (This emulates normal BASH completion behaviour I've observed, though I'm not entirely sure if it's useful)
                    $word = substr($word, 0, strlen($word) + $cursorWordOffset);

                } elseif ($cursorWordOffset > 0) {
                    // Cursor is in the break-space after a word
                    // Push an empty word at the cursor to allow completion of new terms at the cursor, ignoring words ahead
                    $this->wordIndex++;
                    $this->words[] = $word;
                    $this->words[] = '';
                    continue;
                }
            }

            if ($word !== '') {
                $this->words[] = $word;
            }
        }

        if ($this->wordIndex > count($this->words) - 1) {
            $this->wordIndex = count($this->words) - 1;
        }
    }

    /**
     * Reset the computed words so that $this->splitWords is forced to run again
     */
    protected function reset()
    {
        $this->words = null;
        $this->wordIndex = null;
    }
}
