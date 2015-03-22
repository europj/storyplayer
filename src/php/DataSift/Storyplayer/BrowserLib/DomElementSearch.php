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
 * @package   Storyplayer/BrowserLib
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

namespace DataSift\Storyplayer\Browserlib;

use Exception;
use DataSift\Storyplayer\PlayerLib\StoryTeller;
use DataSift\Storyplayer\PlayerLib\Action_LogItem;
use Prose\E5xx_ActionFailed;

/**
 * Retrieve element(s) from the DOM
 *
 * This class is effectively the 'map' part of our map/reduce search pattern:
 *
 * - this class is used to retrieve a list of DOM elements that match our
 *   search criteria
 * - the caller then reduces the list to the single element that is required
 *
 * @category  Libraries
 * @package   Storyplayer/BrowserLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */
class DomElementSearch
{
	public function __construct($st)
	{
		$this->st = $st;
		$this->initDevice();
	}

	// ==================================================================
	//
	// Element finders go here
	//
	// ------------------------------------------------------------------

	/**
	 * @param string $tags
	 */
	protected function convertTagsToString($tags)
	{
		if (is_string($tags)) {
			return $tags;
		}

		return implode('|', $tags);
	}

	// ==================================================================
	//
	// Different ways to find elements in the DOM
	//
	// ------------------------------------------------------------------

	public function getElementsByAltText($text, $tags = '*')
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$tag = $this->convertTagsToString($tags);
		$log = $st->startAction("get '{$tag}' elements with alt text '{$text}'");

		$successMsg = "found one";
		$failureMsg = "no matching elements";

		// prepare the list of tags
		if (is_string($tags)) {
			$tags = array($tags);
		}

		// build up the xpath to use
		$xpathList = array();
		foreach ($tags as $tag) {
			$xpathList[] = 'descendant::' . $tag . '[@alt = "' . $text . '"]';
		}

		// get the possibly matching elements
		$elements = $this->getElementsByXpath($xpathList);

		// log the result
		$log->endAction(count($elements) . " element(s) found");

		// return the elements
		return $elements;
	}

	public function getElementsByClass($class, $tags = '*')
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$tag = $this->convertTagsToString($tags);
		$log = $st->startAction("get '{$tag}' elements with CSS class '{$class}'");

		// prepare the list of tags
		if (is_string($tags)) {
			$tags = array($tags);
		}

		// build up the xpath to use
		$xpathList = array();
		foreach ($tags as $tag) {
			$xpathList[] = 'descendant::' . $tag . '[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]';
		}

		// find the matches
		$elements = $this->getElementsByXpath($xpathList);

		// log the result
		$log->endAction(count($elements) . " element(s) found");

		// return the elements
		return $elements;
	}

	public function getElementsById($id, $tags = '*')
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$tag = $this->convertTagsToString($tags);
		$log = $st->startAction("get '{$tag}' elements with id '{$id}'");

		// prepare the list of tags
		if (is_string($tags)) {
			$tags = array($tags);
		}

		// build up the xpath to use
		$xpathList = array();
		foreach ($tags as $tag) {
			$xpathList[] = 'descendant::' . $tag . '[@id = "' . $id . '"]';
		}

		// find the matches
		$elements = $this->getElementsByXpath($xpathList);

		// log the result
		$log->endAction(count($elements) . " element(s) found");

		// return the elements
		return $elements;
	}

	public function getElementsByLabel($labelText)
	{
		// shorthand
		$st         = $this->st;
		$topElement = $this->getTopElement();

		// what are we doing?
		$log = $st->startAction("get elements for label '{$labelText}'");

		// our return value
		$retval = [];

		try {
			// build up the xpath to use
			$xpathList = [
				'descendant::label[normalize-space(text()) = "' . $labelText . '"]'
			];

			// search using the xpath
			$labelElements = $this->getElementsByXpath($xpathList);

			// we cannot filter by visibility here - the <label> may be
			// visible but the <input> may be invisible :(
		}
		catch (Exception $e) {
			$log->endAction("did not find label '{$labelText}'");
			throw $e;
		}

		// search all of the label elements to find an associated input
		// element that we can safely use
		foreach ($labelElements as $labelElement)
		{
			try {
				// add each element that matches this label
				$retval[] = $this->getElementAssociatedWithLabelElement($labelElement, $labelText);
			}
			catch (Exception $e) {
				// do nothing
			}
		}

		// log the result
		$log->endAction(count($retval) . " element(s) found");

		// return the elements
		return $retval;
	}

	protected function getElementAssociatedWithLabelElement($labelElement, $labelText)
	{
		// shorthand
		$st         = $this->st;
		$topElement = $this->getTopElement();

		// what are we doing?
		$log = $st->startAction("find elements associated with label '$labelText'");

		$inputElementId = null;
		try {
			$inputElementId = $log->addStep("determine id of corresponding input element", function() use($labelElement) {
				return $labelElement->attribute('for');
			});
		}
		catch (Exception $e) {
			usingLog()->writeToLog("label '{$labelText}' is missing the 'for' attribute");

			// this is NOT fatal - the element might be nested
		}

		// what do we do next?
		if ($inputElementId !== null)
		{
			// where does the 'for' attribute go?
			try {
				$inputElement = $log->addStep("find the input element with the id '{$inputElementId}'", function() use($topElement, $inputElementId) {
					return $topElement->getElement('id', $inputElementId);
				});

				// all done
				$log->endAction();
				return $inputElement;
			}
			catch (Exception $e) {

				$log->endAction("could not find element with id '{$inputElementId}'; does markup use 'name' when it should 'id'?");
				// report the failure
				throw new E5xx_ActionFailed(__METHOD__);
			}
		}

		// if we get here, then the label doesn't say which element it is 'for'
		//
		// let's hope (assume?) that the input is inside the element
		try {
			$successMsg = "found nested input";
			$failureMsg = "no visible elements";

			// build up the xpath to use
			$xpathList = [
				'descendant::label[normalize-space(text()) = "' . $labelText . '"]/input'
			];

			// search using the xpath
			$elements = $this->getElementsByXpath($xpathList);

			// find the first one that the user can see
			$inputElement = $this->returnNthVisibleElement(0, $elements);

			// if we get here, we're good
			$log->endAction();
			return $inputElement;
		}
		catch (Exception $e) {
			$log->endAction("cound not find input element associated with label '{$labelText}'");
			throw new E5xx_ActionFailed(__METHOD__);
		}
	}

	public function getElementsByLabelIdOrName($searchTerm, $tags = '*')
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$tag = $this->convertTagsToString($tags);
		$log = $st->startAction("get '{$tag}' with label, id or name '{$searchTerm}'");

		// our return value
		$elements = [];

		// can we find this puppy by its label?
		try {
			$retval = array_merge($retval, $this->getElementsByLabel($searchTerm));
		}
		catch (Exception $e) {
			// do nothing
		}

		// are there any with the ID?
		try {
			$retval = array_merge($this->getElementsById($searchTerm, $tags));
		}
		catch (Exception $e) {
			// do nothing
		}

		// and what about finding it by its text?
		$retval = array_merge($retval, $this->getElementByName($searchTerm, $tags));

		// log the result
		$log->endAction(count($retval) . " element(s) found");

		// return the elements
		return $retval;
	}

	public function getElementsByName($name, $tags = '*')
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$tag = $this->convertTagsToString($tags);
		$log = $st->startAction("get '{$tag}' elements with name '{$name}'");

		// prepare the list of tags
		if (is_string($tags)) {
			$tags = array($tags);
		}

		// build up the xpath to use
		$xpathList = array();
		foreach ($tags as $tag) {
			$xpathList[] = 'descendant::' . $tag . '[@name = "' . $name . '"]';
		}

		// find the matches
		$elements = $this->getElementsByXpath($xpathList);

		// log the result
		$log->endAction(count($elements) . " element(s) found");

		// return the elements
		return $elements;
	}


	public function getElementsByPlaceholder($text, $tags = '*')
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$tag = $this->convertTagsToString($tags);
		$log = $st->startAction("get '{$tag}' element with placeholder '{$text}'");

		$successMsg = "found one";
		$failureMsg = "no matching elements";

		// prepare the list of tags
		if (is_string($tags)) {
			$tags = array($tags);
		}

		// build up the xpath to use
		$xpathList = array();
		foreach ($tags as $tag) {
			$xpathList[] = 'descendant::' . $tag . '[@placeholder = "' . $text . '"]';
		}

		// get the possibly matching elements
		$elements = $this->getElementsByXpath($xpathList);

		// log the result
		$log->endAction(count($elements) . " element(s) found");

		// return the elements
		return $elements;
	}

	public function getElementsByText($text, $tags = '*')
	{
		// short hand
		$st = $this->st;

		// what are we doing?
		$tag = $this->convertTagsToString($tags);
		$log = $st->startAction("get '{$tag}' element with text '{$text}'");

		$successMsg = "found one";
		$failureMsg = "no matching elements";

		// prepare the list of tags
		if (is_string($tags)) {
			$tags = array($tags);
		}

		// build up the xpath to use
		$xpathList = array();
		foreach ($tags as $tag) {
			$xpathList[] = 'descendant::' . $tag . '[normalize-space(text()) = "' . $text . '"]';
			$xpathList[] = 'descendant::' . $tag . '[normalize-space(string(.)) = "' . $text . '"]';
			$xpathList[] = 'descendant::' . $tag . '/*[normalize-space(string(.)) = "' . $text . '"]/parent::' . $tag;

			// special cases
			if ($tag == '*' || $tag == 'input' || $tag == 'button') {
				$xpathList[] = 'descendant::input[normalize-space(@value) = "' . $text . '"]';
				$xpathList[] = 'descendant::input[normalize-space(@placeholder) = "' . $text . '"]';
			}
		}

		// get the possibly matching elements
		$elements = $this->getElementsByXpath($xpathList);

		// log the result
		$log->endAction(count($elements) . " element(s) found");

		// return the elements
		return $elements;
	}

	public function getElementsByTitle($title, $tags = '*')
	{
		// shorthand
		$st = $this->st;

		// what are we doing?
		$tag = $this->convertTagsToString($tags);
		$log = $st->startAction("get '{$tag}' element with title '{$title}'");

		$successMsg = "found one";
		$failureMsg = "no matching elements";

		// prepare the list of tags
		if (is_string($tags)) {
			$tags = array($tags);
		}

		// build up the xpath to use
		$xpathList = array();
		foreach ($tags as $tag) {
			$xpathList[] = 'descendant::' . $tag . '[@title = "' . $title . '"]';
		}

		// search using the xpath
		$elements = $this->getElementsByXpath($xpathList);

		// log the result
		$log->endAction(count($elements) . " element(s) found");

		// return the elements
		return $elements;
	}

	public function getElementsByXpath($xpathList)
	{
		// shorthand
		$st = $this->st;
		$topElement = $this->getTopElement();

		// what are we doing?
		$log = $st->startAction("search the browser's DOM using a list of XPath queries");

		// our set of elements to return
		$return = array();

		try {
			foreach ($xpathList as $xpath) {
				$elements = $log->addStep("find elements using xpath '{$xpath}'", function() use($topElement, $xpath) {
					return $topElement->getElements('xpath', $xpath);
				});

				if (count($elements) > 0) {
					// add these elements to the total list
					$return = array_merge($return, $elements);
				}
			}
		}
		catch (Exception $e) {
			// log the result
			$log->endAction("no matching elements");

			// report the failure
			throw new E5xx_ActionFailed(__METHOD__);
		}

		// if we get here, we found a match
		$log->endAction("found " . count($return) . " element(s)");
		return $return;
	}

	// ==================================================================
	//
	// Support for restricting where we look in the DOM
	//
	// ------------------------------------------------------------------

	protected function initDevice()
	{
		// start the test device
		$this->device = $this->st->getRunningDevice();

		// set our top XPATH node
		//
		// for the moment, we are assuming that the test device is
		// a web browser, because historically this has always been
		// the case
		//
		// when this assumption is no longer valid, we will need to
		// revisit this code
		$this->setTopXpath("//html");

		// set our top element
		//
		// we cannot assume that the browser has any DOM loaded at all
		$this->setTopElement($this->device);
	}

	public function getTopElement()
	{
		return $this->topElement;
	}

	public function setTopElement($element)
	{
		$this->topElement = $element;
	}

	protected function getTopXpath()
	{
		return $this->topXpath;
	}

	/**
	 * @param string $xpath
	 */
	protected function setTopXpath($xpath)
	{
		$this->topXpath = $xpath;
	}

}