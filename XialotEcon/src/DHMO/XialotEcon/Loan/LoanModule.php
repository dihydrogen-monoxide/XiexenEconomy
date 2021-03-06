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

namespace DHMO\XialotEcon\Loan;

use DHMO\XialotEcon\Account\AccountDescriptionEvent;
use DHMO\XialotEcon\Database\ExecuteDutyEvent;
use DHMO\XialotEcon\Database\Queries;
use DHMO\XialotEcon\Init\InitGraph;
use DHMO\XialotEcon\Player\AccountSearchEvent;
use DHMO\XialotEcon\Player\PlayerModule;
use DHMO\XialotEcon\XialotEcon;
use DHMO\XialotEcon\XialotEconModule;
use pocketmine\event\Listener;

final class LoanModule extends XialotEconModule implements Listener{
	public const ACCOUNT_TYPE_SERVER_PLAYER_LOAN = "xialotecon.loan.server2player";
	public const ACCOUNT_TYPE_PLAYER_PLAYER_LOAN = "xialotecon.loan.player2player";

	protected static function getName() : string{
		return "loan";
	}

	protected static function shouldConstruct(XialotEcon $plugin) : bool{
		return $plugin->getConfig()->getNested("loan.enabled", true);
	}

	private $dutyCounter = 0;

	public function __construct(XialotEcon $plugin, InitGraph $graph){
		$this->plugin = $plugin;

		if($plugin->getInitMode() === XialotEcon::INIT_INIT){
			$graph->addGenericQuery("loan.init.loans", Queries::XIALOTECON_LOAN_INIT_LOANS, [], "account.init");
		}
	}

	public function onStartup() : void{
		$this->getPlugin()->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
	}

	public function e_accountSearch(AccountSearchEvent $event) : void{
		$event->pause();
		$this->plugin->getConnector()->executeSelect(Queries::XIALOTECON_ACCOUNT_LOAD_BY_OWNER_TYPE, [
			"ownerType" => PlayerModule::OWNER_TYPE_PLAYER,
			"ownerName" => $event->getPlayerName(),
			"accountTypes" => [
				self::ACCOUNT_TYPE_SERVER_PLAYER_LOAN,
				self::ACCOUNT_TYPE_PLAYER_PLAYER_LOAN,
			],
		], function(array $rows) use ($event){
			foreach($rows as $row){
				$event->addAccountId($row["accountId"], AccountSearchEvent::FLAG_LOAN);
			}
			$event->continue();
		});
	}

	public function e_accountDesc(AccountDescriptionEvent $event) : void{
		switch($event->getAccount()->getAccountType()){
			case self::ACCOUNT_TYPE_SERVER_PLAYER_LOAN:
				$event->setDescription("Loan from server");
				break;
			case self::ACCOUNT_TYPE_PLAYER_PLAYER_LOAN:
				$event->pause();

		}
	}

	public function e_duty(ExecuteDutyEvent $event) : void{
		if($this->dutyCounter % 12 === 0){
			$connector = $this->getPlugin()->getConnector();
			$connector->executeChange(Queries::XIALOTECON_LOAN_DUTY_COMPOUND_NORMAL);
			$connector->executeSelect(Queries::XIALOTECON_LOAN_DUTY_COMPOUND_UNPAID_SERVER, [], function(array $rows){
				// TODO execute minimum payment logic
			});
			$connector->executeSelect(Queries::XIALOTECON_LOAN_DUTY_COMPOUND_UNPAID_SERVER, [], function(array $rows){
				// TODO execute minimum payment logic
			});
		}
		$this->dutyCounter++;
	}
}
