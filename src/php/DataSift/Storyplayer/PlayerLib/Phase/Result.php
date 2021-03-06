<?php

/**
 * Copyright (c) 2011-present Mediasift Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   Storyplayer/PlayerLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

namespace DataSift\Storyplayer\PlayerLib;

use Exception;

/**
 * tracks the result from a single phase
 *
 * a result is a little more than just a PASS/FAIL:
 *
 * 1. we need to know what happened during this phase
 * 2. we need to know how this affects the playing of the story
 * 3. we need to know if there are any other phases we need to execute
 *    *because* we have executed this phase
 * 4. we need to know if there's a message to pass on to the end-user
 *
 * perhaps it was much easier when we just hard-coded all of this?
 *
 * @category  Libraries
 * @package   Storyplayer/PlayerLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

class Phase_Result
{
    protected $phaseName;
    protected $message;
    protected $nextAction;
    protected $result;
    protected $exception;

    public $activityLog = [];

    const MIN_RESULT = 1;
    const MAX_RESULT = 8;

    const COMPLETED    = 1;
    // success is an alias for completed!
    const SUCCEEDED    = 1;
    const FAILED       = 2;
    const INCOMPLETE   = 3;
    const ERROR        = 4;
    const HASNOACTIONS = 5;
    const SKIPPED      = 6;
    const BLACKLISTED  = 7;
    const CANNOTRUN    = 8;

    protected $resultTextMap = [
        1 => "SUCCEEDED",
        2 => "FAILED",
        3 => "INCOMPLETE",
        4 => "ERROR",
        5 => "HASNOACTIONS",
        6 => "SKIPPED",
        7 => "BLACKLISTED",
        8 => "CANNOTRUN",
    ];

    public function __construct($phaseName)
    {
        $this->phaseName = $phaseName;
    }

    /**
     * @return string
     */
    public function getPhaseName()
    {
        return $this->phaseName;
    }

    /**
     * @return boolean
     */
    public function hasMessage()
    {
        if (isset($this->message)) {
            return true;
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        if (!isset($this->exception)) {
            return $this->message;
        }

        // we only want stack traces if an error has occurred
        if ($this->result !== self::ERROR) {
            return $this->message;
        }

        // if we get here, then we want the stack trace too
        return $this->message . PHP_EOL . $this->exception->getTraceAsString();
    }

    /**
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return int
     */
    public function getPhaseResult()
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getPhaseResultString()
    {
        if (isset($this->resultTextMap[$this->result])) {
            return $this->resultTextMap[$this->result];
        }

        return "UNKNOWN";
    }

    /**
     * @return bool
     */
    public function getPhaseCompleted()
    {
        if ($this->result == self::COMPLETED) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getPhaseSucceeded()
    {
        if ($this->result == self::COMPLETED) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getPhaseFailed()
    {
        if ($this->result == self::FAILED) {
            return true;
        }

        return false;
    }

    public function getPhaseHasErrored()
    {
        if ($this->result == self::ERROR) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getPhaseIsIncomplete()
    {
        if ($this->result == self::INCOMPLETE) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getPhaseHasNoActions()
    {
        if ($this->result == self::HASNOACTIONS) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getPhaseIsBlacklisted()
    {
        if ($this->result == self::BLACKLISTED) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getPhaseHasBeenSkipped()
    {
        if ($this->result == self::SKIPPED) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getPhaseCannotRun()
    {
        if ($this->result == self::CANNOTRUN) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getNextAction()
    {
        return $this->nextAction;
    }

    /**
     * @param integer $result
     * @param string|null $msg
     * @param Exception|null $e
     * @return void
     */
    public function setContinuePlaying($result = 1, $msg = null, $e = null)
    {
        $this->nextAction = PhaseGroup_Player::NEXT_CONTINUE;
        $this->result     = $result;
        $this->message    = $msg;
        $this->exception  = $e;
    }

    /**
     * @param integer $result
     * @param string $msg
     * @param Exception|null $e
     * @return void
     */
    public function setPlayingFailed($result, $msg, $e = null)
    {
        $this->nextAction = PhaseGroup_Player::NEXT_FAIL;
        $this->result     = $result;
        $this->message    = $msg;
        $this->exception  = $e;
    }

    /**
     * @param integer $result
     * @param string $msg
     * @return void
     */
    public function setSkipPlaying($result, $msg, $e = null)
    {
        $this->nextAction = PhaseGroup_Player::NEXT_SKIP;
        $this->result     = $result;
        $this->message    = $msg;
        $this->exception  = $e;
    }
}
