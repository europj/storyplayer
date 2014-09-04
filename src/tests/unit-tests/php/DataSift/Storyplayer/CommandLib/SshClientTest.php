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
 * @category    Libraries
 * @package     Storyplayer/CommandLib
 * @author      Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright   2011-present Mediasift Ltd www.datasift.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://datasift.github.io/storyplayer
 */

namespace DataSift\Storyplayer\CommandLib;

use PHPUnit_Framework_TestCase;
use DataSift\Storyplayer\Injectables;
use DataSift\Storyplayer\PlayerLib\StoryTeller;

class SshClientTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers DataSift\Storyplayer\CommandLib\SshClient::__construct
	 */
	public function testCanInstantiate()
	{
		// ----------------------------------------------------------------
		// setup your test

		$i  = new Injectables;
		$i->initOutputSupport();
		$i->initRuntimeConfigSupport($i);
		$st = new StoryTeller($i);

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj = new SshClient($st);

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertTrue($obj instanceof SshClient);
	}

	/**
	 * @covers DataSift\Storyplayer\CommandLib\SshClient::convertParamsForUse
	 */
	public function testCanEscapeParamsForRemoteGlobbing()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$i  = new Injectables;
		$i->initOutputSupport();
		$i->initRuntimeConfigSupport($i);
		$st = new StoryTeller($i);

		$obj   = new SshClient($st);

		$inputParams    = "ls *";
		$expectedParams = "ls '*'";

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualParams = $obj->convertParamsForUse($inputParams);

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedParams, $actualParams);
	}
}