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

namespace DataSift\Storyplayer\Prose;

use DataSift\Stone\ObjectLib\BaseObject;

/**
 * manipulate the internal roles table
 *
 * @category  Libraries
 * @package   Storyplayer/Prose
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */
class UsingRolesTable extends Prose
{
	/**
	 * entryKey
	 * The key that this table interacts with in the RuntimeConfig
	 *
	 * @var string
	 */
	protected $entryKey = "roles";

	/**
	 * addHost
	 *
	 * @param string $hostDetails
	 *        Details about the host to add to the role
	 * @param string $roleName
	 *        Role name to add
	 *
	 * @return void
	 */
	public function addHostToRole($hostDetails, $roleName)
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$log = $st->startAction("add host '{$hostDetails->name}' to role '{$roleName}'");

		// get the existing role details
		$roleDetails =& $st->fromRolesTable()->getDetailsForRole($roleName);

		// is the host already in there?
		$foundHost = false;
		foreach ($roleDetails as $roleDetail) {
			if ($roleDetail->name == $hostDetails->name) {
				$foundHost = true;
			}
		}
		if (!$foundHost) {
			// no, so add it to the end
			$roleDetails[] = $hostDetails;
			var_dump($roleDetails);
			var_dump($st->getRuntimeConfig());
			$st->saveRuntimeConfig();
		}

		// all done
		$log->endAction();
	}

	/**
	 * removeRole
	 *
	 * @param string $roleName
	 *        Role name to remove
	 *
	 * @return void
	 */
	public function removeHostFromRole($hostName, $roleName)
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$log = $st->startAction("remove host '{$hostName}' from '{$roleName}'");

		// which test environment are we working with?
		$testEnvName = $st->getTestEnvironmentName();

		// get the existing role details
		$roleDetails = $st->fromRolesTable()->getDetailsForRole($roleName);

		// do we have this host in the role?
		if (isset($roleDetails->$hostName)) {
			unset($ruleDetails->$hostName);

			// force a save to disk
			$st->saveRuntimeConfig();
		}

		// all done
		$log->endAction();
	}

	public function removeHostFromAllRoles($hostName)
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$log = $st->startAction("remove host '{$hostName}' from all roles");

		// get the roles table
		$roles = $st->fromRolesTable()->getRolesTable();

		// seek and destroy :)
		foreach ($roles as $roleName => $hosts) {
			foreach ($hosts as $hostDetails) {
				if ($hostDetails->name = $hostName) {
					unset ($roles->$roleName->$hostName);
				}
			}
		}

		// force a save, in case anything changed
		$st->saveRuntimeConfig();

		// all done
		$log->endAction();
	}
}