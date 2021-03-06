<?php

/**
 * Copyright (c) 2011-present Mediasift Ltd
 * Copyright (c) 2016-present Ganbaro Digital Ltd
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
 * @package   Storyplayer/TestEnvironments
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @copyright 2016-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

namespace Storyplayer\SPv3\TestEnvironments;

use ReflectionObject;

use GanbaroDigital\Actionables\Values\Actionable;
use StoryplayerInternals\SPv3\Framework\Actionables\BaseTemplate;

/**
 * Base class for reusable test environment setup/teardown instructions
 *
 * @category  Libraries
 * @package   Storyplayer/TestEnvironments
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @copyright 2016-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */
class TestEnvironmentTemplate extends BaseTemplate
{
    /**
     * does our subclass provide a method called 'testEnvironmentSetup'?
     *
     * @return boolean
     *         TRUE if it does
     *         FALSE otherwise
     */
    public function hasTestEnvironmentSetup()
    {
        return method_exists($this, 'testEnvironmentSetup');
    }

    /**
     * does our subclass provide a method called 'testEnvironmentTeardown'?
     *
     * @return boolean
     *         TRUE if it does
     *         FALSE otherwise
     */
    public function hasTestEnvironmentTeardown()
    {
        return method_exists($this, 'testEnvironmentTeardown');
    }

    /**
     * return our 'testEnvironmentSetup' method as a callable
     *
     * @return callable
     */
    public function getTestEnvironmentSetup()
    {
        return new Actionable(
            [$this, 'testEnvironmentSetup'],
            $this->getSourceFilename(),
            [ 'testEnvironmentSetup' ]
        );
    }

    /**
     * return our 'testEnvironmentTeardown' method as a callable
     *
     * @return callable
     */
    public function getTestEnvironmentTeardown()
    {
        return new Actionable(
            [$this, 'testEnvironmentTeardown'],
            $this->getSourceFilename(),
            [ 'testEnvironmentTeardown' ]
        );
    }
}
