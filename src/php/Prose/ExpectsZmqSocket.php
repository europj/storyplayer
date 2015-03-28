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
 * @package   Storyplayer/Prose
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

namespace Prose;

use ZMQ;

/**
 * test ZeroMQ connections
 *
 * @category  Libraries
 * @package   Storyplayer/Prose
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */
class ExpectsZmqSocket extends ZmqSocketBase
{
	public function canSendNonBlocking($message)
	{
		// what are we doing?
		$log = usingLog()->startAction("make sure ZMQ::send() does not block");

		// send the data
		$sent = $this->args[0]->send($message, ZMQ::MODE_NOBLOCK);

		// would it have blocked?
		if (!$sent) {
			throw new E5xx_ExpectFailed(__METHOD__, "send() would not block", "send() would have blocked");
		}

		// all done
		$log->endAction();
	}

	public function canSendmultiNonBlocking($message)
	{
		// what are we doing?
		$log = usingLog()->startAction("make sure ZMQ::sendmulti() does not block");

		// send the data
		$sent = $this->args[0]->sendmulti($message, ZMQ::MODE_NOBLOCK);

		// would it have blocked?
		if (!$sent) {
			throw new E5xx_ExpectFailed(__METHOD__, "sendmulti() would not block", "sendmulti() would have blocked");
		}

		// all done
		$log->endAction();
	}

	public function isConnectedToHost($hostId, $portNumber)
	{
		// what are we doing?
		$log = usingLog()->startAction("make sure ZMQ socket is connected to host '{$hostId}':{$portNumber}");

		// build the address that we should be connected to
		$ipAddress = fromHost($hostId)->getIpAddress();
		$zmqAddress = "tcp://{$ipAddress}:{$portNumber}";

		// where are we connected to?
		$connections = fromZmqSocket($this->args[0])->getEndpoints();

		// make sure we're connected
		assertsArray($connections['connect'])->containsValue($zmqAddress);

		// all done
		$log->endAction();
	}
}