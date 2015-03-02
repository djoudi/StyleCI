<?php

/*
 * This file is part of StyleCI.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 * (c) Joseph Cohen <joseph.cohen@dinkbit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StyleCI\StyleCI\Presenters;

use Illuminate\Contracts\Support\Arrayable;
use McCool\LaravelAutoPresenter\BasePresenter;

/**
 * This is the commit presenter class.
 *
 * @author Graham Campbell <graham@mineuk.com>
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class CommitPresenter extends BasePresenter implements Arrayable
{
    /**
     * Get the commit status summary.
     *
     * @return string
     */
    public function summary()
    {
        switch ($this->wrappedObject->status) {
            case 1:
                return 'PASSED';
            case 2:
                return 'FAILED';
            case 3:
                return 'ERRORED';
            case 4:
                return 'MISCONFIGURED';
            default:
                return 'PENDING';
        }
    }

    /**
     * Get the commit's repo shorthand id.
     *
     * @return string
     */
    public function shorthandId()
    {
        return substr($this->wrappedObject->id, 0, 6);
    }

    /**
     * Get the commit's time ago.
     *
     * @return string
     */
    public function timeAgo()
    {
        return $this->wrappedObject->created_at->diffForHumans();
    }

    /**
     * Get the commit's created time ISO.
     *
     * @return string
     */
    public function createdAtToISO()
    {
        return $this->wrappedObject->created_at->toIso8601String();
    }

    /**
     * Get the diff splited to files.
     *
     * @return array
     */
    public function diffFiles()
    {
        if (trim($this->wrappedObject->diff) == '') {
            return [];
        }

        $diff = ltrim($this->wrappedObject->diff);

        // We first get the original and modified file names from the diff.
        preg_match_all('/\-\-\-\sa\/(.*?.*)/i', $diff, $originalNames);
        preg_match_all('/\+\+\+\sb\/(.*?.*)/i', $diff, $modifiedNames);

        $fileNames = [];

        // If the file name was modified we show the change.
        foreach ($originalNames[1] as $key => $originalName) {
            if ($originalName != $modifiedNames[1][$key]) {
                $fileNames[] = $originalName.' -> '.$modifiedNames[1][$key];
            } else {
                $fileNames[] = $originalName;
            }
        }

        // Then we split the diff into files.
        $extractedFiles = preg_split("/\s+?diff ?/", $diff);

        $files = [];

        // Match file names with files.
        foreach ($extractedFiles as $index => $file) {
            $files[$fileNames[$index]] = preg_replace('/^.+\n.+\n/', '', $file);
        }

        return $files;
    }

    /**
     * Convert presented commit to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'             => $this->wrappedObject->id,
            'repo_id'        => $this->wrappedObject->repo_id,
            'repo_name'      => $this->wrappedObject->repo->name,
            'message'        => $this->wrappedObject->message,
            'description'    => $this->wrappedObject->description(),
            'status'         => $this->wrappedObject->status,
            'summary'        => $this->summary(),
            'timeAgo'        => $this->timeAgo(),
            'shorthandId'    => $this->shorthandId(),
            'createdAtToISO' => $this->createdAtToISO(),
            'link'           => route('commit_path', $this->wrappedObject->id),
        ];
    }
}
