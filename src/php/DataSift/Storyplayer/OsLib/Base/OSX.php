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
 * @package   Storyplayer/OsLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

namespace DataSift\Storyplayer\OsLib;

use DataSift\Stone\ObjectLib\BaseObject;
use DataSift\Storyplayer\HostLib\SupportedHost;
use GanbaroDigital\TextTools\Filters\FilterColumns;
use GanbaroDigital\TextTools\Filters\FilterForMatchingRegex;
use GanbaroDigital\TextTools\Filters\FilterForMatchingString;
use Storyplayer\SPv2\Modules\Exceptions;
use Storyplayer\SPv2\Modules\Log;

/**
 * support for Storyplayer running on OSX
 *
 * @category  Libraries
 * @package   Storyplayer/Prose
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

abstract class Base_OSX extends OsBase
{
    /**
     *
     * @param  HostDetails $hostDetails
     * @param  SupportedHost $host
     * @return string
     */
    public function determineIpAddress($hostDetails, SupportedHost $host)
    {
        throw Exceptions::newActionFailedException(__METHOD__, "not supported");
    }

    public function determineHostname($hostDetails, SupportedHost $host)
    {
        // what are we doing?
        $log = Log::usingLog()->startAction("query " . basename(__CLASS__) . " for hostname");

        // how do we do this?
        $command = "hostname";
        $result = $host->runCommandViaHostManager($hostDetails, $command);

        if ($result->didCommandSucceed()) {
            $lines = explode("\n", $result->output);
            $hostname = trim($lines[0]);
            $hostname = $this->runHostnameSafeguards($hostDetails, $hostname);
            $log->endAction("hostname is '{$hostname}'");
            return $hostname;
        }

        // if we get here, we do not know what the hostname is
        $msg = "could not determine hostname";
        $log->endAction($msg);
        throw Exceptions::newActionFailedException(__METHOD__, $msg);
    }

    /**
     *
     * @param  HostDetails $hostDetails
     * @param  string $packageName
     * @return BaseObject
     */
    public function getInstalledPackageDetails($hostDetails, $packageName)
    {
        throw Exceptions::newActionFailedException(__METHOD__, "not supported");
    }

    /**
     *
     * @param  HostDetails $hostDetails
     * @param  string $processName
     * @return boolean
     */
    public function getProcessIsRunning($hostDetails, $processName)
    {
        // what are we doing?
        $log = Log::usingLog()->startAction("is process '{$processName}' running on OSX '{$hostDetails->hostId}'?");

        // SSH in and have a look
        $command   = "ps -ef | grep '{$processName}'";
        $result    = $this->runCommand($hostDetails, $command);

        // what did we find?
        if ($result->didCommandFail() || empty($result->output)) {
            $log->endAction("not running");
            return false;
        }

        // process the result
        $lines = explode("\n", $result->output);
        $lines = FilterColumns::from($lines, "7", ' ');
        $lines = FilterForMatchingRegex::against($lines, "|^{$processName}$|");
        if (empty($lines)) {
            $log->endAction("not running");
            return false;
        }

        // success
        $log->endAction("is running");
        return true;
    }

    /**
     *
     * @param  HostDetails $hostDetails
     * @param  string $processName
     * @return integer
     */
    public function getPid($hostDetails, $processName)
    {
        // log some info to the user
        $log = Log::usingLog()->startAction("get pid for process '{$processName}' running on OSX machine '{$hostDetails->hostId}'");

        // run the command to get the process id
        $command   = "ps -ef | grep '{$processName}'";
        $result    = $this->runCommand($hostDetails, $command);

        // check that we got something
        if ($result->didCommandFail() || empty($result->output)) {
            $log->endAction("could not get pid ... is the process running?");
            return 0;
        }

        // check that we found exactly one process
        $pids = explode("\n", $result->output);
        $pids = FilterForMatchingRegex::against($pids, "|{$processName}|");
        $pids = FilterColumns::from($pids, '1', ' ');
        if (count($pids) != 1) {
            $log->endAction("found more than one process but expecting only one ... is this correct?");
            return $pids[0];
        }

        // we can now reason that we have the correct pid
        $pid = $pids[0];

        // all done
        $log->endAction("{$pid}");
        return $pid;
    }

    /**
     *
     * @param  HostDetails $hostDetails
     * @param  int $pid
     * @return boolean
     */
    public function getPidIsRunning($hostDetails, $pid)
    {
        // what are we doing?
        $log = Log::usingLog()->startAction("is process PID '{$pid}' running on OSX '{$hostDetails->hostId}'?");

        // SSH in and have a look
        $command   = "ps -ef | grep '{$pid}'";
        $result    = $this->runCommand($hostDetails, $command);

        // what did we find?
        if ($result->didCommandFail() || empty($result->output)) {
            $log->endAction("not running");
            return false;
        }

        // examine the output
        $lines = explode("\n", $result->output);
        $lines = FilterColumns::from($lines, "1", ' ');
        $lines = FilterForMatchingRegex::against($lines, "|^{$pid}$|");

        // did we find the process?
        if (empty($lines)) {
            $log->endAction("not running");
            return false;
        }

        // success
        $log->endAction("is running");
        return true;
    }

}
