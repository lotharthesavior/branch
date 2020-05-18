<?php


namespace Git\DTO;


class Status
{

    /** @var string */
    protected $rawMessage;

    /** @var string */
    protected $branch;

    /** @var array */
    protected $newAssets;

    /** @var array */
    protected $changes;

    /**
     * @param string $cliRawMessage
     */
    public function __construct(string $cliRawMessage)
    {
        $this->rawMessage = $cliRawMessage;
        $this->parseRawMessage($cliRawMessage);
    }

    /**
     * @return string
     */
    public function getRawMessage(): string
    {
        return $this->rawMessage;
    }

    /**
     * @return string
     */
    public function getBranch(): string
    {
        return $this->branch;
    }

    /**
     * @return array
     */
    public function getNewAssets(): array
    {
        return $this->newAssets;
    }

    /**
     * @return array
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * Goes through each line and analyze it by "section".
     * There are 2 sections after the branch declaration:
     *   1. changes declaration
     *   2. new assets declaration
     * 
     * @param string $cliRawMessage
     * @return void
     */
    private function parseRawMessage(string $cliRawMessage): void
    {
        $cliRawMessage = explode("\n", $cliRawMessage);

        $newAssets = [];
        $changes = [];
        $extraLines = [];

        $isChangesToBeCommited = false;
        $isUntrackedFiles = false;

        $item = 0;
        while(count($cliRawMessage) > $item) {
            $line = $cliRawMessage[$item];

            if (empty(trim($line)) || $this->isCommitsDeclaration($line)) {
                $item++;
                continue;
            }

            if (
                $this->isBranchDeclaration($line)
                && !$isChangesToBeCommited
                && !$isUntrackedFiles
            ) {
                $this->branch = $this->getBranchFromDeclaration($line);
                $item++;
                continue;
            }

            if (
                $this->isChangesToBeCommitedSection($line)
            ) {
                $isUntrackedFiles = false;
                $isChangesToBeCommited = true;
                $item++;
                continue;
            }

            if (
                $this->isUntrackedFilesSection($line)
            ) {
                $isUntrackedFiles = true;
                $isChangesToBeCommited = false;
                $item++;
                continue;
            }

            if (
                ($isChangesToBeCommited || $isUntrackedFiles)
                && $this->isProcessExplanation($line)
            ) {
                $item++;
                continue;
            }

            if ($isChangesToBeCommited) {
                $changes[] = $this->getFileDeclaration($line);
                $item++;
                continue;
            }

            if ($isUntrackedFiles) {
                $newAssets[] = $this->getFileDeclaration($line);
                $item++;
                continue;
            }

            $item++;
        }

        $this->changes = $changes;
        $this->newAssets = $newAssets;
    }

    /**
     * @param string $statusLine
     * @return boolean
     */
    private function isBranchDeclaration(string $statusLine): bool
    {
        return strstr($statusLine, 'On branch ') !== false;
    }

    /**
     * @param string $statusLine
     * @return string
     */
    private function getBranchFromDeclaration(string $statusLine): string
    {
        return str_replace('On branch ', '', $statusLine);
    }

    /**
     * @param string $statusLine
     * @return boolean
     */
    private function isCommitsDeclaration(string $statusLine): bool
    {
        return strstr($statusLine, 'No commits yet') !== false;
    }

    /**
     * @param string $statusLine
     * @return boolean
     */
    private function isChangesToBeCommitedSection(string $statusLine): bool
    {
        return strstr($statusLine, 'Changes to be committed') !== false
            || strstr($statusLine, 'Changes not staged for commit') !== false;
    }

    /**
     * @param string $statusLine
     * @return boolean
     */
    private function isUntrackedFilesSection(string $statusLine): bool
    {
        return strstr($statusLine, 'Untracked files') !== false;
    }

    /**
     * @todo this might get removed
     * @param string $statusLine
     * @return boolean
     */
    private function getFileDeclaration(string $statusLine): string
    {
        if (strstr($statusLine, 'new file:') !== false) {
            $statusLine = str_replace('new file:', '', $statusLine);
        }

        if (strstr($statusLine, 'modified:') !== false) {
            $statusLine = str_replace('modified:', '', $statusLine);
        }

        return trim($statusLine);
    }

    /**
     * @param string $statusLine
     * @return string
     */
    private function isProcessExplanation(string $statusLine): string
    {
        $possibleExplanations = [
            'git add <file>',
            'git rm --cached',
            'nothing added to commit',
            'git checkout -- <file>',
            'no changes added to commit',
            'git restore <file>',
        ];

        return count(array_filter($possibleExplanations, function($item) use ($statusLine) {
            return strstr($statusLine, $item) !== false;
        })) > 0;
    }

}
