<?php

/**
 * XialotEcon
 *
 * Copyright (C) 2017-2018 dihydrogen-monoxide
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

declare(strict_types=1);

namespace DHMO\XialotEcon\Debug;

use DHMO\XialotEcon\XialotEcon;
use DHMO\XialotEcon\XialotEconModule;
use poggit\libasynql\libasynql;

class DebugModule extends XialotEconModule{
	protected static function getName() : string{
		return "debug";
	}

	public static function shouldConstruct(XialotEcon $plugin) : bool{
		return !libasynql::isPackaged();
	}

	protected function __construct(XialotEcon $plugin, callable $onComplete){
		$this->plugin = $plugin;
		$onComplete();
	}

	public function onStartup() : void{
		$this->plugin->getServer()->getCommandMap()->registerAll("xialotecon_debug", [
			new ListCacheCommand("list-cache", "List DataModel cache", "/list-cache"),
		]);
	}
}