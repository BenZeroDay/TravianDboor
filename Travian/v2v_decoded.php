<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."v2v.php" );
require_once( MODEL_PATH."build.php" );
class GPage extends VillagePage
{

	public $pageState = NULL;
	public $targetVillage = array
	(
		"x" => NULL,
		"y" => NULL
	);
	public $troops = NULL;
	public $disableFirstTwoAttack = FALSE;
	public $attackWithCatapult = FALSE;
	public $transferType = 2;
	public $errorTable = array( );
	public $newVillageResources = array
	(
		1 => 750,
		2 => 750,
		3 => 750,
		4 => 750
	);
	public $rallyPointLevel = 0;
	public $totalCatapultTroopsCount = 0;
	public $catapultCanAttackLastIndex = 0;
	public $availableCatapultTargetsString = "";
	public $catapultCanAttack = array
	(
		0 => 0,
		1 => 10,
		2 => 11,
		3 => 9,
		4 => 6,
		5 => 2,
		6 => 4,
		7 => 8,
		8 => 7,
		9 => 3,
		10 => 5,
		11 => 1,
		12 => 22,
		13 => 13,
		14 => 19,
		15 => 12,
		16 => 35,
		17 => 18,
		18 => 29,
		19 => 30,
		20 => 37,
		21 => 41,
		22 => 15,
		23 => 17,
		24 => 26,
		25 => 16,
		26 => 25,
		27 => 20,
		28 => 14,
		29 => 24,
		30 => 28,
		31 => 40,
		32 => 21
	);
	public $onlyOneSpyAction = FALSE;
	public $backTroopsProperty = array( );

	public function GPage( )
	{
		parent::villagepage( );
		$this->viewFile = "v2v.phtml";
		$this->contentCssClass = "a2b";
	}

	public function onLoadBuildings( $building )
	{
		if ( $building['item_id'] == 16 && $this->rallyPointLevel < $building['level'] )
		{
			$this->rallyPointLevel = $building['level'];
		}
	}

	public function load( )
	{
		parent::load( );
		if ( $this->rallyPointLevel <= 0 )
		{
			$this->redirect( "build.php?id=39" );
		}
		else if ( isset( $_GET['d1'] ) || isset( $_GET['d2'] ) || isset( $_GET['d3'] ) )
		{
			$this->pageState = 3;
			$this->handleTroopBack( );
		}
		else
		{
			$m = new WarModel( );
			$this->pageState = 1;
			$map_size = $this->setupMetadata['map_size'];
			$half_map_size = floor( $map_size / 2 );
			$this->hasHero = $this->data['hero_in_village_id'] == $this->data['selected_village_id'];
			$t_arr = explode( "|", $this->data['troops_num'] );
			foreach ( $t_arr as $t_str )
			{
				$t2_arr = explode( ":", $t_str );
				if ( $t2_arr[0] == 0 - 1 )
				{
					$t2_arr = explode( ",", $t2_arr[1] );
					foreach ( $t2_arr as $t2_str )
					{
						$t = explode( " ", $t2_str );
						if ( $t[0] == 99 )
						{
							continue;
						}
						$this->troops[] = array(
							"troopId" => $t[0],
							"number" => $t[1]
						);
					}
				}
			}
			$attackOptions1 = "";
			$sendTroops = FALSE;
			$playerData = NULL;
			$villageRow = NULL;
			if ( !$this->isPost( ) )
			{
				if ( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) )
				{
					$vid = intval( $_GET['id'] );
					if ( $vid < 1 )
					{
						$vid = 1;
					}
					$villageRow = $m->getVillageDataById( $vid );
				}
			}
			else if ( isset( $_POST['id'] ) )
			{
				$sendTroops = !$this->isGameTransientStopped( ) && !$this->isGameOver( );
				$vid = intval( $_POST['id'] );
				$villageRow = $m->getVillageDataById( $vid );
			}
			else if ( isset( $_POST['dname'] ) && trim( $_POST['dname'] ) != "" )
			{
				$villageRow = $m->getVillageDataByName( trim( $_POST['dname'] ) );
			}
			else if ( isset( $_POST['x'], $_POST['y'] ) && trim( $_POST['x'] ) != "" && trim( $_POST['y'] ) != "" )
			{
				$vid = $this->__getVillageId( $map_size, $this->__getCoordInRange( $map_size, intval( $_POST['x'] ) ), $this->__getCoordInRange( $map_size, intval( $_POST['y'] ) ) );
				$villageRow = $m->getVillageDataById( $vid );
			}    
			if ( $villageRow == NULL )
			{
				if ( $this->isPost( ) )
				{
					$this->errorTable = v2v_p_entervillagedata;
				}
			}
			else
			{
				$this->disableFirstTwoAttack = intval( $villageRow['player_id'] ) == 0 && $villageRow['is_oasis'];
				$this->targetVillage['x'] = floor( ( $villageRow['id'] - 1 ) / $map_size );
				$this->targetVillage['y'] = $villageRow['id'] - ( $this->targetVillage['x'] * $map_size + 1 );
				if ( $half_map_size < $this->targetVillage['x'] )
				{
					$this->targetVillage['x'] -= $map_size;
				}
				if ( $half_map_size < $this->targetVillage['y'] )
				{
					$this->targetVillage['y'] -= $map_size;
				}
				if ( $villageRow['id'] == $this->data['selected_village_id'] )
				{
					return;
				}
				if ( 0 < intval( $villageRow['player_id'] ) && $m->getPlayType( $villageRow['player_id'] ) == PLAYERTYPE_ADMIN )
				{
					return;
				}
				$spyOnly = FALSE;
				if ( !$villageRow['is_oasis'] && intval( $villageRow['player_id'] ) == 0 )
				{
					$this->transferType = 1;
					$humanTroopId = 0;
					$renderTroops = array( );
					foreach ( $this->troops as $troop )
					{
						$renderTroops[$troop['troopId']] = 0;
						if ( $troop['troopId'] == 10 || $troop['troopId'] == 20 || $troop['troopId'] == 30 || $troop['troopId'] == 109 || $troop['troopId'] == 60 )
						{
							$humanTroopId = $troop['troopId'];
							$renderTroops[$humanTroopId] = $troop['number'];
						}
					}
					$canBuildNewVillage = isset( $renderTroops[$humanTroopId] ) && 3 <= $renderTroops[$humanTroopId];
					if ( $canBuildNewVillage )
					{
						$count = trim( $this->data['child_villages_id'] ) == "" ? 0 : sizeof( explode( ",", $this->data['child_villages_id'] ) );
						if ( 2 < $count )
						{
							$this->errorTable = v2v_p_cannotbuildnewvill;
						}
						else if ( !$this->_canBuildNewVillage( ) )
						{
							$this->errorTable = v2v_p_cannotbuildnewvill1;
						}
						else if ( !$this->isResourcesAvailable( $this->newVillageResources ) )
						{
							$this->errorTable = sprintf( v2v_p_cannotbuildnewvill2, $this->newVillageResources['1'] );
						}
						else if ( !$m->hasNewVillageTask( $this->player->playerId ) )
						{
							break;
						}
						else
						{
							$this->errorTable = v2v_p_cannotbuildnewvill3;
						}
					}
					else
					{
						do
						{
							$this->errorTable = v2v_p_cannotbuildnewvill4;
							return;
						} while ( 0 );
						$this->pageState = 2;
					}
				}
				else if ( $this->isPost( ) )
				{
					if ( !$villageRow['is_oasis'] && intval( $villageRow['player_id'] ) == 0 )
					{
						$this->errorTable = v2v_p_novillagehere;
					}
					else
					{
						if ( !isset( $_POST['c'] ) || intval( $_POST['c'] ) < 1 || 4 < intval( $_POST['c'] ) )
						{
							return;
						}
						$this->transferType = $this->disableFirstTwoAttack ? 4 : intval( $_POST['c'] );
						if ( 0 < intval( $villageRow['player_id'] ) )
						{
							$playerData = $m->getPlayerDataById( intval( $villageRow['player_id'] ) );
							if ( $playerData['is_blocked'] )
							{
								$this->errorTable = v2v_p_playerwas_blocked;
							}
							else if ( 0 < $playerData['protection_remain_sec'] )
							{
								$this->errorTable = v2v_p_playerwas_inprotectedperiod;
							}
						}
						else
						{
							$totalTroopsCount = 0;
							$totalSpyTroopsCount = 0;
							$this->totalCatapultTroopsCount = 0;
							$hasTroopsSelected = FALSE;
							$renderTroops = array( );
							if ( isset( $_POST['t'] ) )
							{
								foreach ( $this->troops as $troop )
								{
									$num = 0;
									if ( isset( $_POST['t'][$troop['troopId']] ) && 0 < intval( $_POST['t'][$troop['troopId']] ) )
									{
										$num = $troop['number'] < $_POST['t'][$troop['troopId']] ? $troop['number'] : intval( $_POST['t'][$troop['troopId']] );
									}
									$renderTroops[$troop['troopId']] = $num;
									$totalTroopsCount += $num;
									if ( 0 < $num )
									{
										$hasTroopsSelected = TRUE;
									}
									if ( $troop['troopId'] == 4 || $troop['troopId'] == 14 || $troop['troopId'] == 23 || $troop['troopId'] == 103 || $troop['troopId'] == 54 )
									{
										$totalSpyTroopsCount += $num;
									}
									else if ( $troop['troopId'] == 8 || $troop['troopId'] == 18 || $troop['troopId'] == 28 || $troop['troopId'] == 107 || $troop['troopId'] == 58 )
									{
										$this->totalCatapultTroopsCount += $num;
									}
								}
							}
							if ( $this->hasHero && isset( $_POST['_t'] ) && intval( $_POST['_t'] ) == 1 )
							{
								$hasTroopsSelected = TRUE;
								$totalTroopsCount += 1;
							}
							$spyOnly = $totalSpyTroopsCount == $totalTroopsCount && ( $this->transferType == 3 || $this->transferType == 4 ) && 0 < intval( $villageRow['player_id'] );
							if ( $spyOnly )
							{
								$this->onlyOneSpyAction = $villageRow['is_oasis'];
							}
							$this->attackWithCatapult = 0 < $this->totalCatapultTroopsCount && $this->transferType == 3 && 0 < intval( $villageRow['player_id'] ) && !$villageRow['is_oasis'];
							if ( $this->attackWithCatapult )
							{
								if ( 10 <= $this->rallyPointLevel )
								{
									$this->catapultCanAttackLastIndex = sizeof( $this->catapultCanAttack ) - 1;
								}
								else if ( 5 <= $this->rallyPointLevel )
								{
									$this->catapultCanAttackLastIndex = 11;
								}
								else if ( 3 <= $this->rallyPointLevel )
								{
									$this->catapultCanAttackLastIndex = 2;
								}
								else
								{
									$this->catapultCanAttackLastIndex = 0;
								}
								$attackOptions1 = isset( $_POST['dtg'] ) && $this->_containBuildingTarget( $_POST['dtg'] ) ? intval( $_POST['dtg'] ) : 0;
								if ( $this->rallyPointLevel == 20 && 20 <= $this->totalCatapultTroopsCount )
								{
									$attackOptions1 = "2:".( $attackOptions1." ".( isset( $_POST['dtg1'] ) && $this->_containBuildingTarget( $_POST['dtg1'] ) ? intval( $_POST['dtg1'] ) : 0 ) );
								}
								else
								{
									$attackOptions1 = "1:".$attackOptions1;
								}
								$this->availableCatapultTargetsString = "";
								$selectComboTargetOptions = "";
								$i = 1;
								while ( $i <= 9 )
								{
									if ( $this->_containBuildingTarget( $i ) )
									{
										$selectComboTargetOptions .= sprintf( "<option value=\"%s\">%s</option>", $i, constant( "item_".$i ) );
									}
									++$i;
								}
								if ( $selectComboTargetOptions != "" )
								{
									$this->availableCatapultTargetsString .= "<optgroup label=\"".v2v_p_catapult_grp1."\">".$selectComboTargetOptions."</optgroup>";
								}
								$selectComboTargetOptions = "";
								$i = 10;
								while ( $i <= 28 )
								{
									if ( ( $i == 10 || $i == 11 || $i == 15 || $i == 17 || $i == 18 || $i == 24 || $i == 25 || $i == 26 || $i == 28 || $i == 38 || $i == 39 ) && $this->_containBuildingTarget( $i ) )
									{
										$selectComboTargetOptions .= sprintf( "<option value=\"%s\">%s</option>", $i, constant( "item_".$i ) );
									}
									++$i;
								}
								if ( $selectComboTargetOptions != "" )
								{
									$this->availableCatapultTargetsString .= "<optgroup label=\"".v2v_p_catapult_grp2."\">".$selectComboTargetOptions."</optgroup>";
								}
								$selectComboTargetOptions = "";
								$i = 12;
								while ( $i <= 37 )
								{
									if ( ( $i == 12 || $i == 13 || $i == 14 || $i == 16 || $i == 19 || $i == 20 || $i == 21 || $i == 22 || $i == 35 || $i == 37 ) && $this->_containBuildingTarget( $i ) )
									{
										$selectComboTargetOptions .= sprintf( "<option value=\"%s\">%s</option>", $i, constant( "item_".$i ) );
									}
									++$i;
								}
								if ( $selectComboTargetOptions != "" )
								{
									$this->availableCatapultTargetsString .= "<optgroup label=\"".v2v_p_catapult_grp3."\">".$selectComboTargetOptions."</optgroup>";
								}
							}
							if ( !$hasTroopsSelected )
							{
								$this->errorTable = v2v_p_thereisnoattacktroops;
							}
							else
							{
								$this->pageState = 2;
							}
						}
					}
				}
				if ( $this->pageState == 2 )
				{
					$this->targetVillage['transferType'] = $this->transferType == 1 ? v2v_p_attacktyp1 : $this->transferType == 2 ? v2v_p_attacktyp2." " : $this->transferType == 3 ? v2v_p_attacktyp3 : $this->transferType == 4 ? v2v_p_attacktyp4 : "";
					if ( $villageRow['is_oasis'] )
					{
						$this->targetVillage['villageName'] = $playerData != NULL ? v2v_p_placetyp1 : v2v_p_placetyp2;
					}
					else
					{
						$this->targetVillage['villageName'] = $playerData != NULL ? $villageRow['village_name'] : v2v_p_placetyp3;
					}
					$this->targetVillage['villageId'] = $villageRow['id'];
					$this->targetVillage['playerName'] = $playerData != NULL ? $playerData['name'] : $villageRow['is_oasis'] ? v2v_p_monster : "";
					$this->targetVillage['playerId'] = 0;
					$this->targetVillage['troops'] = $renderTroops;
					$this->targetVillage['hasHero'] = 1 < $this->transferType && $this->hasHero && isset( $_POST['_t'] ) && intval( $_POST['_t'] ) == 1;
					$distance = WebHelper::getdistance( $this->data['rel_x'], $this->data['rel_y'], $this->targetVillage['x'], $this->targetVillage['y'], $this->setupMetadata['map_size'] / 2 );
					$this->targetVillage['needed_time'] = intval( $distance / $this->_getTheSlowestTroopSpeed( $renderTroops ) * 3600 );
					$this->targetVillage['spy'] = $spyOnly;
				}
				if ( $sendTroops )
				{
					$taskType = 0;
					switch ( $this->transferType )
					{
					case 1 :
						$taskType = QS_CREATEVILLAGE;
						break;
					case 2 :
						$taskType = QS_WAR_REINFORCE;
						break;
					case 3 :
						$taskType = QS_WAR_ATTACK;
						break;
					case 4 :
					
					$taskType = QS_WAR_ATTACK_PLUNDER;
					break; 
					return;
                    }   
					$spyAction = 0;
					if ( $spyOnly )
					{
						$taskType = QS_WAR_ATTACK_SPY;
						$spyAction = isset( $_POST['spy'] ) && ( intval( $_POST['spy'] ) == 1 || intval( $_POST['spy'] ) == 2 ) ? intval( $_POST['spy'] ) : 1;
						if ( $this->onlyOneSpyAction )
						{
							$spyAction = 1;
						}
					}
					$troopsStr = "";
					foreach ( $this->targetVillage['troops'] as $tid => $tnum )
					{
						if ( $troopsStr != "" )
						{
							$troopsStr .= ",";
						}
						$troopsStr .= $tid." ".$tnum;
					}
					if ( $this->targetVillage['hasHero'] )
					{
						$troopsStr .= ",".$this->data['hero_troop_id']." -1";
					}
					$catapultTargets = $attackOptions1;
					$carryResources = $taskType == QS_CREATEVILLAGE ? implode( " ", $this->newVillageResources ) : "";
					$procParams = $troopsStr."|".( $this->targetVillage['hasHero'] ? 1 : 0 )."|".$spyAction."|".$catapultTargets."|".$carryResources."|||0";
					$newTask = new QueueTask( $taskType, $this->player->playerId, $this->targetVillage['needed_time'] );
					$newTask->villageId = $this->data['selected_village_id'];
					$newTask->toPlayerId = intval( $villageRow['player_id'] );
					$newTask->toVillageId = $villageRow['id'];
					$newTask->procParams = $procParams;
					$newTask->tag = array(
						"troops" => $this->targetVillage['troops'],
						"hasHero" => $this->targetVillage['hasHero'],
						"resources" => $taskType == QS_CREATEVILLAGE ? $this->newVillageResources : NULL
					);
					$this->queueModel->addTask( $newTask );
					$m->dispose( );
					$this->redirect( "build.php?id=39" );
				}
				else
				{
					$m->dispose( );
				}
			}
		}
	}

	public function handleTroopBack( )
	{
		$qstr = "";
		$fromVillageId = 0;
		$toVillageId = 0;
		$action = 0;
		if ( isset( $_GET['d1'] ) )
		{
			$action = 1;
			$qstr = "d1=".intval( $_GET['d1'] );
			if ( isset( $_GET['o'] ) )
			{
				$qstr .= "&o=".intval( $_GET['o'] );
				$fromVillageId = intval( $_GET['o'] );
			}
			else
			{
				$fromVillageId = $this->data['selected_village_id'];
			}
			$toVillageId = intval( $_GET['d1'] );
		}
		else if ( isset( $_GET['d2'] ) )
		{
			$action = 2;
			$qstr = "d2=".intval( $_GET['d2'] );
			$fromVillageId = $this->data['selected_village_id'];
			$toVillageId = intval( $_GET['d2'] );
		}
		else if ( isset( $_GET['d3'] ) )
		{
			$action = 3;
			$qstr = "d3=".intval( $_GET['d3'] );
			$fromVillageId = intval( $_GET['d3'] );
			$toVillageId = $this->data['selected_village_id'];
		}
		else
		{
			$this->redirect( "build.php?id=39" );
			return;
		}
		$this->backTroopsProperty['queryString'] = $qstr;
		$m = new WarModel( );
		$fromVillageData = $m->getVillageData2ById( $fromVillageId );
		$toVillageData = $m->getVillageData2ById( $toVillageId );
		if ( $fromVillageData == NULL || $toVillageData == NULL )
		{
			$m->dispose( );
			$this->redirect( "build.php?id=39" );
		}
		else
		{
			$vid = $toVillageId;
			$_backTroopsStr = "";
			$this->backTroopsProperty['headerText'] = v2v_p_backtroops;
			$this->backTroopsProperty['action1'] = "<a href=\"village3.php?id=".$fromVillageData['id']."\">".$fromVillageData['village_name']."</a>";
			$this->backTroopsProperty['action2'] = "<a href=\"profile.php?uid=".$fromVillageData['player_id']."\">".v2v_p_troopsinvillagenow."</a>";
			$column1 = "";
			$column2 = "";
			if ( $action == 1 )
			{
				$_backTroopsStr = $fromVillageData['troops_num'];
				$column1 = "troops_num";
				$column2 = "troops_out_num";
			}
			else if ( $action == 2 )
			{
				$this->backTroopsProperty['headerText'] = v2v_p_backcaptivitytroops;
				$_backTroopsStr = $fromVillageData['troops_intrap_num'];
				$column1 = "troops_intrap_num";
				$column2 = "troops_out_intrap_num";
			}
			else if ( $action == 3 )
			{
				$_backTroopsStr = $toVillageData['troops_out_num'];
				$vid = $fromVillageId;
				$column1 = "troops_num";
				$column2 = "troops_out_num";
			}
			$this->backTroopsProperty['backTroops'] = $this->_getTroopsForVillage( $_backTroopsStr, $vid );
			if ( $this->backTroopsProperty['backTroops'] == NULL )
			{
				$m->dispose( );
				$this->redirect( "build.php?id=39" );
			}
			else
			{
				$distance = WebHelper::getdistance( $fromVillageData['rel_x'], $fromVillageData['rel_y'], $toVillageData['rel_x'], $toVillageData['rel_y'], $this->setupMetadata['map_size'] / 2 );
				if ( $this->isPost( ) )
				{
					$canSend = FALSE;
					$troopsGoBack = array( );
					foreach ( $this->backTroopsProperty['backTroops']['troops'] as $tid => $tnum )
					{
						if ( isset( $_POST['t'], $_POST['t'][$tid] ) )
						{
							$selNum = intval( $_POST['t'][$tid] );
							if ( $selNum < 0 )
							{
								$selNum = 0;
							}
							if ( $tnum < $selNum )
							{
								$selNum = $tnum;
							}
							$troopsGoBack[$tid] = $selNum;
							if ( 0 < $selNum )
							{
								$canSend = TRUE;
							}
						}
						else
						{
							$troopsGoBack[$tid] = 0;
						}
					}
					$sendTroopsArray = array(
						"troops" => $troopsGoBack,
						"hasHero" => FALSE,
						"heroTroopId" => 0
					);
					$hasHeroTroop = $this->backTroopsProperty['backTroops']['hasHero'] && isset( $_POST['_t'] ) && intval( $_POST['_t'] ) == 1;
					if ( $hasHeroTroop )
					{
						$sendTroopsArray['hasHero'] = TRUE;
						$sendTroopsArray['heroTroopId'] = $this->backTroopsProperty['backTroops']['heroTroopId'];
						$canSend = TRUE;
					}
					if ( !$canSend )
					{
						$m->dispose( );
						$this->redirect( "build.php?id=39" );
					}
					else if ( !$this->isGameTransientStopped( ) && !$this->isGameOver( ) )
					{
						$troops1 = $this->_getTroopsAfterReduction( $fromVillageData[$column1], $toVillageId, $sendTroopsArray );
						$troops2 = $this->_getTroopsAfterReduction( $toVillageData[$column2], $fromVillageId, $sendTroopsArray );
						$m->backTroopsFrom( $fromVillageId, $column1, $troops1, $toVillageId, $column2, $troops2 );
						$timeInSeconds = intval( $distance / $this->_getTheSlowestTroopSpeed2( $sendTroopsArray ) * 3600 );
						$procParams = $this->_getTroopAsString( $sendTroopsArray )."|0||||||1";
						$newTask = new QueueTask( QS_WAR_REINFORCE, intval( $fromVillageData['player_id'] ), $timeInSeconds );
						$newTask->villageId = $fromVillageId;
						$newTask->toPlayerId = intval( $toVillageData['player_id'] );
						$newTask->toVillageId = $toVillageId;
						$newTask->procParams = $procParams;
						$newTask->tag = array(
							"troops" => NULL,
							"hasHero" => FALSE,
							"resources" => NULL
						);
						$affectCropConsumption = TRUE;
						if ( $fromVillageData['is_oasis'] && trim( $toVillageData['village_oases_id'] ) != "" )
						{
							$oArr = explode( ",", trim( $toVillageData['village_oases_id'] ) );
							foreach ( $oArr as $oid )
							{
								if ( !( $oid == $fromVillageData['id'] ) )
								{
									continue;
								}
								$affectCropConsumption = FALSE;
								break;
								break;
							}
						}
						if ( $affectCropConsumption )
						{
							$newTask->tag['troopsCropConsume'] = $this->_getTroopCropConsumption( $sendTroopsArray );
						}
						$this->queueModel->addTask( $newTask );
						$m->dispose( );
						$this->redirect( "build.php?id=39" );
						return;
					}
				}
				else
				{
					$this->backTroopsProperty['time'] = intval( $distance / $this->_getTheSlowestTroopSpeed2( $this->backTroopsProperty['backTroops'] ) * 3600 );
				}
				$m->dispose( );
			}
		}
	}

	public function _getTroopCropConsumption( $troopsArray )
	{
		$GameMetadata = $GLOBALS['GameMetadata'];
		$consume = 0;
		foreach ( $troopsArray['troops'] as $tid => $tnum )
		{
			$consume += $GameMetadata['troops'][$tid]['crop_consumption'] * $tnum;
		}
		if ( $troopsArray['hasHero'] )
		{
			$consume += $GameMetadata['troops'][$troopsArray['heroTroopId']]['crop_consumption'];
		}
		return $consume;
	}

	public function _getTroopAsString( $troopsArray )
	{
		$str = "";
		foreach ( $troopsArray['troops'] as $tid => $num )
		{
			if ( $str != "" )
			{
				$str .= ",";
			}
			$str .= $tid." ".$num;
		}
		if ( $troopsArray['hasHero'] )
		{
			if ( $str != "" )
			{
				$str .= ",";
			}
			$str .= $troopsArray['heroTroopId']." -1";
		}
		return $str;
	}

	public function _getTroopsAfterReduction( $troopString, $targetVillageId, $sendTroopsArray )
	{
		if ( trim( $troopString ) == "" )
		{
			return "";
		}
		$reductionTroopsString = "";
		$t_arr = explode( "|", $troopString );
		foreach ( $t_arr as $t_str )
		{
			$t2_arr = explode( ":", $t_str );
			if ( $t2_arr[0] == $targetVillageId )
			{
				$completelyBacked = TRUE;
				$newTroopStr = "";
				$t2_arr = explode( ",", $t2_arr[1] );
				foreach ( $t2_arr as $t2_str )
				{
					
                    list( $tid, $tnum ) = explode( " ", $t2_str );					
                    if ( $tnum == 0 - 1 )
					{
						if ( !$sendTroopsArray['hasHero'] )
						{
							if ( $newTroopStr != "" )
							{
								$newTroopStr .= ",";
							}
							$newTroopStr .= $tid." ".$tnum;
							$completelyBacked = FALSE;
						}
					}
					else
					{
						if ( isset( $sendTroopsArray['troops'][$tid] ) )
						{
							$n = $sendTroopsArray['troops'][$tid];
							if ( $n < 0 )
							{
								$n = 0;
							}
							if ( $tnum < $n )
							{
								$n = $tnum;
							}
							$tnum -= $n;
							if ( 0 < $tnum )
							{
								$completelyBacked = FALSE;
							}
						}
						if ( $newTroopStr != "" )
						{
							$newTroopStr .= ",";
						}
						$newTroopStr .= $tid." ".$tnum;
					}
				}
				if ( !$completelyBacked )
				{
					if ( $reductionTroopsString != "" )
					{
						$reductionTroopsString .= "|";
					}
					$reductionTroopsString .= $targetVillageId.":".$newTroopStr;
				}
			}
			else
			{
				if ( $reductionTroopsString != "" )
				{
					$reductionTroopsString .= "|";
				}
				$reductionTroopsString .= $t_str;
			}
		}
		return $reductionTroopsString;
	}

	public function _getTroopsForVillage( $troopString, $villageId )
	{
		if ( trim( $troopString ) == "" )
		{
			return NULL;
		}
		$t_arr = explode( "|", $troopString );
		foreach ( $t_arr as $t_str )
		{
			$t2_arr = explode( ":", $t_str );
			if ( !( $t2_arr[0] == $villageId ) )
			{
				continue;
			}
			$troopTable = array(
				"hasHero" => FALSE,
				"heroTroopId" => 0,
				"troops" => array( )
			);
			$t2_arr = explode( ",", $t2_arr[1] );
			foreach ( $t2_arr as $t2_str )
			{
				list( $tid, $tnum ) = explode( " ", $t2_str );				
                if ( $tid == 99 )
				{
					continue;
				}
				if ( $tnum == 0 - 1 )
				{
					$troopTable['heroTroopId'] = $tid;
					$troopTable['hasHero'] = TRUE;
					continue;
				}
				$troopTable['troops'][$tid] = $tnum;
			}
			return $troopTable;
		}
		return NULL;
	}

	public function _getMaxBuildingLevel( $itemId )
	{
		$result = 0;
		foreach ( $this->buildings as $villageBuild )
		{
			if ( $villageBuild['item_id'] == $itemId && $result < $villageBuild['level'] )
			{
				$result = $villageBuild['level'];
			}
		}
		return $result;
	}

	public function _getTheSlowestTroopSpeed2( $troopsArray )
	{
		$minSpeed = 0 - 1;
		foreach ( $troopsArray['troops'] as $tid => $num )
		{
			if ( 0 < $num )
			{
				$speed = $this->gameMetadata['troops'][$tid]['velocity'];
				if ( $minSpeed == 0 - 1 || $speed < $minSpeed )
				{
					$minSpeed = $speed;
				}
			}
		}
		if ( $troopsArray['hasHero'] )
		{
			$htid = $troopsArray['heroTroopId'];
			$speed = $this->gameMetadata['troops'][$htid]['velocity'];
			if ( $minSpeed == 0 - 1 || $speed < $minSpeed )
			{
				$minSpeed = $speed;
			}
		}
		$blvl = $this->_getMaxBuildingLevel( 14 );
		$factor = $blvl == 0 ? 100 : $this->gameMetadata['items'][14]['levels'][$blvl - 1]['value'];
		$factor *= $this->gameMetadata['game_speed'];
		return $minSpeed * ( $factor / 100 );
	}

	public function _getTheSlowestTroopSpeed( $troopsArray )
	{
		$minSpeed = 0 - 1;
		foreach ( $troopsArray as $tid => $num )
		{
			if ( 0 < $num )
			{
				$speed = $this->gameMetadata['troops'][$tid]['velocity'];
				if ( $minSpeed == 0 - 1 || $speed < $minSpeed )
				{
					$minSpeed = $speed;
				}
			}
		}
		if ( $this->hasHero && isset( $_POST['_t'] ) && intval( $_POST['_t'] ) == 1 )
		{
			$htid = $this->data['hero_troop_id'];
			$speed = $this->gameMetadata['troops'][$htid]['velocity'];
			if ( $minSpeed == 0 - 1 || $speed < $minSpeed )
			{
				$minSpeed = $speed;
			}
		}
		$blvl = $this->_getMaxBuildingLevel( 14 );
		$factor = $blvl == 0 ? 100 : $this->gameMetadata['items'][14]['levels'][$blvl - 1]['value'];
		$factor *= $this->gameMetadata['game_speed'];
		return $minSpeed * ( $factor / 100 );
	}

	public function _canBuildNewVillage( )
	{
		$GameMetadata = $GLOBALS['GameMetadata'];
		$neededCpValue = $totalCpRate = $totalCpValue = 0;
		$m = new BuildModel( );
		$result = $m->getVillagesCp( $this->data['villages_id'] );
		while ( $result->next( ) )
		{
			list( $cpValue, $cpRate ) = explode( " ", $result->row['cp'] );			
            $cpValue += $result->row['elapsedTimeInSeconds'] * ( $cpRate / 86400 );
			$totalCpRate += $cpRate;
			$totalCpValue += $cpValue;
			$neededCpValue += intval( $this->gameMetadata['cp_for_new_village'] / $GameMetadata['game_speed'] );
		}
		$totalCpValue = floor( $totalCpValue );
		$m->dispose( );
		return $neededCpValue <= $totalCpValue;
	}

	public function __getCoordInRange( $map_size, $x )
	{
		if ( $map_size <= $x )
		{
			$x -= $map_size;
		}
		else if ( $x < 0 )
		{
			$x = $map_size + $x;
		}
		return $x;
	}

	public function __getVillageId( $map_size, $x, $y )
	{
		return $x * $map_size + ( $y + 1 );
	}

	public function _containBuildingTarget( $item_id )
	{
		$i = 0;
		while ( $i <= $this->catapultCanAttackLastIndex )
		{
			if ( $this->catapultCanAttack[$i] == $item_id )
			{
				return TRUE;
			}
			++$i;
		}
		return FALSE;
	}

}

$p = new GPage( );
$p->run( );
?>
