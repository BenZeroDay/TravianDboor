<?php
require('.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php');
require_once(MODEL_PATH . 'build.php');
class GPage extends VillagePage
    {
    var $productionPane = TRUE;
    var $buildingView = '';
    var $buildingIndex = -1;
    var $buildProperties = NULL;
    var $newBuilds = NULL;
    var $troopsUpgrade = null;
    var $troopsUpgradeType = null;
    var $buildingTribeFactor = null;
    var $troops = array();
    var $selectedTabIndex = 0;
    var $villageOases = null;
    var $childVillages = null;
    var $hasHero = FALSE;
    
    var $totalCpRate = null;
    var $totalCpValue = null;
    var $neededCpValue = null;
    var $childVillagesCount = null;
    var $showBuildingForm = null;
    var $embassyProperty = null;
    var $merchantProperty = null;
    var $rallyPointProperty = null;
    var $crannyProperty = array('buildingCount' => 0, 'totalSize' => 0);
    var $warriorMessage = '';
    var $dataList = null;
    var $pageSize = 40;
    var $pageCount = null;
    var $pageIndex = null;
    function GPage()
        {
        parent::villagepage();
        $this->viewFile        = 'build.phtml';
        $this->contentCssClass = 'build';
        }
    function onLoadBuildings($building)
        {
        $GameMetadata = $GLOBALS['GameMetadata'];
        if (((($this->buildingIndex == 0 - 1 && isset($_GET['bid'])) && is_numeric($_GET['bid'])) && $_GET['bid'] == $building['item_id']))
            {
            $this->buildingIndex = $building['index'];
            }
        if (($building['item_id'] == 23 && 0 < $building['level']))
            {
            ++$this->crannyProperty['buildingCount'];
            $this->crannyProperty['totalSize'] += $GameMetadata['items'][$building['item_id']]['levels'][$building['level'] - 1]['value'] * $GameMetadata['items'][$building['item_id']]['for_tribe_id'][$this->tribeId];
            }
        }
    function load()
        {
        parent::load();
        if (((($this->buildingIndex == 0 - 1 && isset($_GET['id'])) && is_numeric($_GET['id'])) && isset($this->buildings[$_GET['id']])))
            {
            $this->buildingIndex = clean(intval($_GET['id']));
            }
        $this->buildProperties = $this->getBuildingProperties($this->buildingIndex);
        if ($this->buildProperties == NULL)
            {
            $this->redirect('village1.php');
            return null;
            }
        if ($this->buildProperties['emptyPlace'])
            {
            $this->villagesLinkPostfix .= '&id=' . $this->buildingIndex;
            $this->newBuilds = array(
                'available' => array(),
                'soon' => array()
            );
            foreach ($this->gameMetadata['items'] as $item_id => $build)
                {
                if (($item_id <= 4 || !isset($build['for_tribe_id'][$this->tribeId])))
                    {
                    continue;
                    }
                $canBuild = $this->canCreateNewBuild($item_id);
                if ($canBuild != 0 - 1)
                    {
                    if ($canBuild)
                        {
                        if (!isset($this->newBuilds['available'][$build['levels'][0]['time_consume']]))
                            {
                            $this->newBuilds['available'][$build['levels'][0]['time_consume']] = array();
                            }
                        $this->newBuilds['available'][$build['levels'][0]['time_consume']][$item_id] = $build;
                        }
                    $dependencyCount = 0;
                    foreach ($build['pre_requests'] as $reqId => $reqValue)
                        {
                        if ($reqValue != NULL)
                            {
                            $build['pre_requests_dependencyCount'][$reqId] = $reqValue - $this->_getMaxBuildingLevel($reqId);
                            $dependencyCount += $build['pre_requests_dependencyCount'][$reqId];
                            }
                        }
                    if (!isset($this->newBuilds['soon'][$dependencyCount]))
                        {
                        $this->newBuilds['soon'][$dependencyCount] = array();
                        }
                    $this->newBuilds['soon'][$dependencyCount][$item_id] = $build;
                    }
                }
            ksort($this->newBuilds['available'], SORT_NUMERIC);
            ksort($this->newBuilds['soon'], SORT_NUMERIC);
            return null;
            }
        $bitemId = $this->buildProperties['building']['item_id'];
        $this->villagesLinkPostfix .= '&id=' . $this->buildingIndex;
        if (4 < $bitemId)
            {
            $this->villagesLinkPostfix .= '&bid=' . $bitemId;
            }
        $this->buildingTribeFactor = (isset($this->gameMetadata['items'][$bitemId]['for_tribe_id'][$this->data['tribe_id']]) ? $this->gameMetadata['items'][$bitemId]['for_tribe_id'][$this->data['tribe_id']] : 1);
        if ($this->buildings[$this->buildingIndex]['level'] == 0)
            {
            return null;
            }
        switch ($bitemId)
        {
            case 12:
            case 13:
                $this->productionPane = FALSE;
                $this->buildingView   = 'Blacksmith_Armoury';
                $this->handleBlacksmithArmoury();
                break;
            case 15:
                if (10 <= $this->buildings[$this->buildingIndex]['level'])
                    {
                    $this->buildingView = 'MainBuilding';
                    $this->handleMainBuilding();
                    }
                break;
            case 16:
                $this->productionPane = FALSE;
                $this->buildingView   = 'RallyPoint';
                $this->handleRallyPoint();
                break;
            case 17:
                $this->productionPane = FALSE;
                $this->buildingView   = 'Marketplace';
                $this->handleMarketplace();
                break;
            case 18:
                $this->productionPane = FALSE;
                $this->buildingView   = 'Embassy';
                $this->handleEmbassy();
                break;
            case 19:
            case 20:
            case 21:
            case 29:
            case 30:
            case 36:
                $this->_getOnlyMyTroops();
                $this->productionPane = $bitemId == 36;
                $this->buildingView   = 'TroopBuilding';
                $this->handleTroopBuilding();
                break;
            case 22:
                $this->productionPane = FALSE;
                $this->buildingView   = 'Academy';
                $this->handleAcademy();
                break;
            case 23:
                $this->productionPane = TRUE;
                $this->buildingView   = 'Cranny';
                break;
            case 24:
                $this->productionPane = FALSE;
                $this->buildingView   = 'TownHall';
                $this->handleTownHall();
                break;
            case 25:
            case 26:
                $this->productionPane = FALSE;
                $this->buildingView   = 'Residence_Palace';
                $this->handleResidencePalace();
                break;
             case 27:
                $this->productionPane = FALSE;
                $this->buildingView   = 'Treasury';
                $this->handleTreasury();
                break;
            case 37:
                $this->productionPane = FALSE;
                $this->buildingView   = 'HerosMansion';
                $this->handleHerosMansion();
                break;
            case 40:
                $this->productionPane = FALSE;
                break;
            case 42:
                $this->_getOnlyMyTroops();
                $this->productionPane = TRUE;
                $this->buildingView   = 'Warrior';
                $this->handleWarrior();
        }
        }
    function handleBlacksmithArmoury()
        {
        $this->troopsUpgradeType = ($this->buildings[$this->buildingIndex]['item_id'] == 12 ? QS_TROOP_UPGRADE_ATTACK : QS_TROOP_UPGRADE_DEFENSE);
        $this->troopsUpgrade     = array();
        $_arr                    = explode(',', $this->data['troops_training']);
        $_c                      = 0;
        foreach ($_arr as $troopStr)
            {
            ++$_c;
            list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
            $tlevel = ($this->troopsUpgradeType == QS_TROOP_UPGRADE_ATTACK ? $attack_level : $defense_level);
            if (((($troopId != 99 && $_c <= 8) && $tlevel < 20) && $researches_done == 1))
                {
                $this->troopsUpgrade[$troopId] = $tlevel;
                }
            }
        if (((((((isset($_GET['a']) && isset($_GET['k'])) && $_GET['k'] == $this->data['update_key']) && !isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType])) && isset($this->troopsUpgrade[intval($_GET['a'])])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
            {
            $troopId          = clean(intval($_GET['a']));
            $level            = $this->troopsUpgrade[$troopId];
            $buildingMetadata = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']]['troop_upgrades'][$troopId][$level];
            if (!$this->isResourcesAvailable($buildingMetadata['resources']))
                {
                return null;
                }
            $calcConsume         = intval($buildingMetadata['time_consume'] / $this->gameSpeed * (10 / ($this->buildProperties['building']['level'] + 9)));
            $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
            $newTask->villageId  = $this->data['selected_village_id'];
            $newTask->procParams = $troopId . ' ' . ($level + 1);
            $newTask->tag        = $buildingMetadata['resources'];
            $this->queueModel->addTask($newTask);
            }
        }
    function handleMainBuilding()
        {
        if ((((((((($this->isPost() && isset($_POST['drbid'])) && 19 <= intval($_POST['drbid'])) && intval($_POST['drbid']) <= sizeof($this->buildings)) && isset($this->buildings[$_POST['drbid']])) && 0 < $this->buildings[$_POST['drbid']]['level']) && !isset($this->queueModel->tasksInQueue[QS_BUILD_DROP])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
            {
            $item_id             = $this->buildings[clean($_POST['drbid'])]['item_id'];
            $calcConsume         = intval($this->gameMetadata['items'][$item_id]['levels'][$this->buildings[$_POST['drbid']]['level'] - 1]['time_consume'] / $this->gameSpeed * ($this->data['time_consume_percent'] / 400));
            $newTask             = new QueueTask(QS_BUILD_DROP, $this->player->playerId, $calcConsume);
            $newTask->villageId  = $this->data['selected_village_id'];
            $newTask->buildingId = $item_id;
            $newTask->procParams = $this->buildings[$_POST['drbid']]['index'];
            $this->queueModel->addTask($newTask);
            return null;
            }
        if ((((((((isset($_GET['qid']) && is_numeric($_GET['qid'])) && isset($_GET['k'])) && $_GET['k'] == $this->data['update_key']) && isset($_GET['d'])) && isset($this->queueModel->tasksInQueue[QS_BUILD_DROP])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
            {
            $this->queueModel->cancelTask($this->player->playerId, intval($_GET['qid']));
            }
        }
    function handleRallyPoint()
        {
        if (isset($_GET['d']))
            {
            $this->queueModel->cancelTask($this->player->playerId, intval($_GET['d']));
            }
        $this->rallyPointProperty = array(
            'troops_in_village' => array(
                'troopsTable' => $this->_getTroopsList('troops_num'),
                'troopsIntrapTable' => $this->_getTroopsList('troops_intrap_num')
            ),
            'troops_out_village' => array(
                'troopsTable' => $this->_getTroopsList('troops_out_num'),
                'troopsIntrapTable' => $this->_getTroopsList('troops_out_intrap_num')
            ),
            'troops_in_oases' => array(),
            'war_to_village' => $this->queueModel->tasksInQueue['war_troops']['to_village'],
            'war_from_village' => $this->queueModel->tasksInQueue['war_troops']['from_village'],
            'war_to_oasis' => $this->queueModel->tasksInQueue['war_troops']['to_oasis']
        );
        $village_oases_id         = trim($this->data['village_oases_id']);
        if ($village_oases_id != '')
            {
            $m      = new BuildModel();
            $result = $m->getOasesDataById($village_oases_id);
            while ($result->next())
                {
                $this->rallyPointProperty['troops_in_oases'][$result->row['id']] = array(
                    'oasisRow' => $result->row,
                    'troopsTable' => $this->_getOasisTroopsList($result->row['troops_num']),
                    'war_to' => (isset($this->rallyPointProperty['war_to_oasis'][$result->row['id']]) ? $this->rallyPointProperty['war_to_oasis'][$result->row['id']] : NULL)
                );
                }
            $m->dispose();
            }
        }
    function _canCancelWarTask($taskType, $taskId)
        {
        if (!QueueTask::iscancelabletask($taskType))
            {
            return FALSE;
            }
        $timeout = QueueTask::getmaxcanceltimeout($taskType);
        if (0 - 1 < $timeout)
            {
            $_task = NULL;
            foreach ($this->queueModel->tasksInQueue[$taskType] as $t)
                {
                if ($t['id'] == $taskId)
                    {
                    $_task = $t;
                    break;
                    }
                }
            if ($_task == NULL)
                {
                return FALSE;
                }
            $elapsedTime = $t['elapsedTime'];
            if ($timeout < $elapsedTime)
                {
                return FALSE;
                }
            }
        return TRUE;
        }
    function _getOasisTroopsList($troops_num)
        {
        $GameMetadata = $GLOBALS['GameMetadata'];
        $m            = new BuildModel();
        $returnTroops = array();
        if (trim($troops_num) != '')
            {
            $t_arr = explode('|', $troops_num);
            foreach ($t_arr as $t_str)
                {
                $t2_arr             = explode(':', $t_str);
                $vid                = $t2_arr[0];
                $villageData        = $m->getVillageData2ById($vid);
                $returnTroops[$vid] = array(
                    'villageData' => $villageData,
                    'cropConsumption' => 0,
                    'hasHero' => FALSE,
                    'troops' => array()
                );
                $t2_arr             = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str)
                    {
                    list($tid, $tnum) = explode(' ', $t2_str);
                    if ($tid == 99)
                        {
                        continue;
                        }
                    if ($tnum == 0 - 1)
                        {
                        $tnum                          = 1;
                        $returnTroops[$vid]['hasHero'] = TRUE;
                        }
                    else
                        {
                        $returnTroops[$vid]['troops'][$tid] = $tnum;
                        }
                    $returnTroops[$vid]['cropConsumption'] += $GameMetadata['troops'][$tid]['crop_consumption'] * $tnum;
                    }
                }
            }
        $m->dispose();
        return $returnTroops;
        }
    function _getTroopsList($key)
        {
        $GameMetadata = $GLOBALS['GameMetadata'];
        $m            = new BuildModel();
        $returnTroops = array();
        if (trim($this->data[$key]) != '')
            {
            $t_arr = explode('|', $this->data[$key]);
            foreach ($t_arr as $t_str)
                {
                $t2_arr      = explode(':', $t_str);
                $vid         = intval($t2_arr[0]);
                $villageData = NULL;
                if ($vid == 0 - 1)
                    {
                    $vid         = $this->data['selected_village_id'];
                    $villageData = array(
                        'id' => $vid,
                        'village_name' => $this->data['village_name'],
                        'player_id' => $this->player->playerId,
                        'player_name' => buildings_p_thisvillage
                    );
                    }
                else
                    {
                    $villageData = $m->getVillageData2ById($vid);
                    }
                $returnTroops[$vid] = array(
                    'villageData' => $villageData,
                    'cropConsumption' => 0,
                    'hasHero' => FALSE,
                    'troops' => array()
                );
                if ($vid == $this->data['selected_village_id'])
                    {
                    $returnTroops[$vid]['hasHero'] = intval($this->data['hero_in_village_id']) == intval($this->data['selected_village_id']);
                    if ($returnTroops[$vid]['hasHero'])
                        {
                        $returnTroops[$vid]['cropConsumption'] += $GameMetadata['troops'][$this->data['hero_troop_id']]['crop_consumption'];
                        }
                    }
                $t2_arr = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str)
                    {
                    list($tid, $tnum) = explode(' ', $t2_str);
                    if ($tid == 99)
                        {
                        continue;
                        }
                    if ($tnum == 0 - 1)
                        {
                        $tnum                          = 1;
                        $returnTroops[$vid]['hasHero'] = TRUE;
                        }
                    else
                        {
                        $returnTroops[$vid]['troops'][$tid] = $tnum;
                        }
                    $returnTroops[$vid]['cropConsumption'] += $GameMetadata['troops'][$tid]['crop_consumption'] * $tnum;
                    }
                }
            }
        $m->dispose();
        return $returnTroops;
        }
    function handleMarketplace()
        {
        $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 1 <= intval($_GET['t'])) && intval($_GET['t']) <= 3) ? intval($_GET['t']) : 0);
        $itemId                 = $this->buildings[$this->buildingIndex]['item_id'];
        $itemLevel              = $this->buildings[$this->buildingIndex]['level'];
        $tribeMetadata          = $this->gameMetadata['tribes'][$this->data['tribe_id']];
        $tradeOfficeLevel       = $this->_getMaxBuildingLevel(28);
        $capacityFactor         = ($tradeOfficeLevel == 0 ? 1 : $this->gameMetadata['items'][28]['levels'][$tradeOfficeLevel - 1]['value'] / 100);
        $capacityFactor *= $this->gameMetadata['game_speed'];
        $total_merchants_num = $this->gameMetadata['items'][$itemId]['levels'][$itemLevel - 1]['value'];
        $exist_num           = $total_merchants_num - $this->queueModel->tasksInQueue['out_merchants_num'] - $this->data['offer_merchants_count'];
        if ($exist_num < 0)
            {
            $exist_num = 0;
            }
        $this->merchantProperty = array(
            'speed' => $tribeMetadata['merchants_velocity'] * $this->gameMetadata['game_speed'],
            'capacity' => floor($tribeMetadata['merchants_capacity'] * $capacityFactor),
            'total_num' => $total_merchants_num,
            'exits_num' => $exist_num,
            'confirm_snd' => FALSE,
            'same_village' => FALSE,
            'vRow' => NULL
        );
        if ($this->selectedTabIndex == 0)
            {
            $m = new BuildModel();
            if (($this->isPost() || isset($_GET['vid2'])))
                {
                $resources                             = array(
                    '1' => (isset($_POST['r1']) ? intval($_POST['r1']) : 0),
                    '2' => (isset($_POST['r2']) ? intval($_POST['r2']) : 0),
                    '3' => (isset($_POST['r3']) ? intval($_POST['r3']) : 0),
                    '4' => (isset($_POST['r4']) ? intval($_POST['r4']) : 0)
                );
                $this->merchantProperty['confirm_snd'] = ($this->isPost() ? (isset($_POST['act']) && $_POST['act'] == 1) : isset($_GET['vid2']));
                $map_size                              = $this->setupMetadata['map_size'];
                $doSend                                = FALSE;
                if ($this->merchantProperty['confirm_snd'])
                    {
                    $vRow = NULL;
                    if ((((isset($_POST['x']) && isset($_POST['y'])) && trim($_POST['x']) != '') && trim($_POST['y']) != ''))
                        {
                        $vid  = $this->__getVillageId($map_size, $this->__getCoordInRange($map_size, intval($_POST['x'])), $this->__getCoordInRange($map_size, intval($_POST['y'])));
                        $vRow = $m->getVillageDataById($vid);
                        }
                    else if ((isset($_POST['vname']) && trim($_POST['vname']) != ''))
                        {
                        $vRow = $m->getVillageDataByName(trim($_POST['vname']));
                        }
                    else if (isset($_GET['vid2']))
                        {
                        $vRow = $m->getVillageDataById(intval($_GET['vid2']));
                        if ($vRow != NULL)
                            {
                            $_POST['x'] = $vRow['rel_x'];
                            $_POST['y'] = $vRow['rel_y'];
                            }
                        }
                    }
                else
                    {
                    $doSend                              = TRUE;
                    $vRow                                = $m->getVillageDataById(intval($_POST['vid2']));
                    $this->merchantProperty['showError'] = FALSE;
                    $_POST['r1']                         = $_POST['r2'] = $_POST['r3'] = $_POST['r4'] = '';
                    }
                if ((0 < intval($vRow['player_id']) && $m->getPlayType(intval($vRow['player_id'])) == PLAYERTYPE_ADMIN))
                    {
                    $this->merchantProperty['showError']   = FALSE;
                    $this->merchantProperty['confirm_snd'] = FALSE;
                    return null;
                    }
                $this->merchantProperty['vRow']              = $vRow;
                $vid                                         = $this->merchantProperty['to_vid'] = ($vRow != NULL ? $vRow['id'] : 0);
                $rel_x                                       = $vRow['rel_x'];
                $rel_y                                       = $vRow['rel_y'];
                $this->merchantProperty['same_village']      = $vid == $this->data['selected_village_id'];
                $this->merchantProperty['available_res']     = $this->isResourcesAvailable($resources);
                $this->merchantProperty['vRow_merchant_num'] = ceil(($resources[1] + $resources[2] + $resources[3] + $resources[4]) / $this->merchantProperty['capacity']);
                $this->merchantProperty['confirm_snd']       = ((((0 < $vid && $this->merchantProperty['available_res']) && 0 < $this->merchantProperty['vRow_merchant_num']) && $this->merchantProperty['vRow_merchant_num'] <= $this->merchantProperty['exits_num']) && !$this->merchantProperty['same_village']);
                $this->merchantProperty['showError']         = !$this->merchantProperty['confirm_snd'];
                $distance                                    = WebHelper::getdistance($this->data['rel_x'], $this->data['rel_y'], $rel_x, $rel_y, $this->setupMetadata['map_size'] / 2);
                $this->merchantProperty['vRow_time']         = intval($distance / $this->merchantProperty['speed'] * 3600);
                if ((((!$this->merchantProperty['showError'] && $doSend) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
                    {
                    $this->merchantProperty['confirm_snd'] = FALSE;
                    $this->merchantProperty['exits_num'] -= $this->merchantProperty['vRow_merchant_num'];
                    $newTask              = new QueueTask(QS_MERCHANT_GO, $this->player->playerId, $this->merchantProperty['vRow_time']);
                    $newTask->villageId   = $this->data['selected_village_id'];
                    $newTask->toPlayerId  = $vRow['player_id'];
                    $newTask->toVillageId = $vid;
                    $newTask->procParams  = $this->merchantProperty['vRow_merchant_num'] . '|' . ($resources[1] . ' ' . $resources[2] . ' ' . $resources[3] . ' ' . $resources[4]);
                    $newTask->tag         = $resources;
                    $this->queueModel->addTask($newTask);
                    }
                }
            $m->dispose();
            return null;
            }
        if ($this->selectedTabIndex == 1)
            {
            $m             = new BuildModel();
            $showOfferList = TRUE;
            if ((isset($_GET['oid']) && 0 < intval($_GET['oid'])))
                {
                $oRow = $m->getOffer2(intval($_GET['oid']), $this->data['rel_x'], $this->data['rel_y'], $this->setupMetadata['map_size'] / 2);
                if ($oRow != NULL)
                    {
                    $aid = 0;
                    if ($oRow['alliance_only'])
                        {
                        if (0 < intval($this->data['alliance_id']))
                            {
                            $aid = $m->getPlayerAllianceId($oRow['player_id']);
                            }
                        }
                    list($res1, $res2) = explode('|', $oRow['offer']);
                    $resArr1       = explode(' ', $res1);
                    $needResources = array(
                        '1' => $resArr1[0],
                        '2' => $resArr1[1],
                        '3' => $resArr1[2],
                        '4' => $resArr1[3]
                    );
                    $res1_item_id  = 0;
                    $res1_value    = 0;
                    $i             = 0;
                    $_c            = sizeof($resArr1);
                    while ($i < $_c)
                        {
                        if (0 < $resArr1[$i])
                            {
                            $res1_item_id = $i + 1;
                            $res1_value   = $resArr1[$i];
                            break;
                            }
                        ++$i;
                        }
                    $resArr1       = explode(' ', $res2);
                    $giveResources = array(
                        '1' => $resArr1[0],
                        '2' => $resArr1[1],
                        '3' => $resArr1[2],
                        '4' => $resArr1[3]
                    );
                    $res2_item_id  = 0;
                    $res2_value    = 0;
                    $i             = 0;
                    $_c            = sizeof($resArr1);
                    while ($i < $_c)
                        {
                        if (0 < $resArr1[$i])
                            {
                            $res2_item_id = $i + 1;
                            $res2_value   = $resArr1[$i];
                            break;
                            }
                        ++$i;
                        }
                    $distance     = $oRow['timeInSeconds'] / 3600 * $oRow['merchants_speed'];
                    $acceptResult = $this->_canAcceptOffer($needResources, $giveResources, $oRow['village_id'], $oRow['alliance_only'], $aid, $oRow['max_time'], $distance);
                    if ((($acceptResult == 5 && !$this->isGameTransientStopped()) && !$this->isGameOver()))
                        {
                        $showOfferList                           = FALSE;
                        $this->merchantProperty['offerProperty'] = array(
                            'player_id' => $oRow['player_id'],
                            'player_name' => $oRow['player_name'],
                            'res1_item_id' => $res1_item_id,
                            'res1_value' => $res1_value,
                            'res2_item_id' => $res2_item_id,
                            'res2_value' => $res2_value
                        );
                        $merchantNum                             = ceil(($giveResources[1] + $giveResources[2] + $giveResources[3] + $giveResources[4]) / $this->merchantProperty['capacity']);
                        $newTask                                 = new QueueTask(QS_MERCHANT_GO, $this->player->playerId, $distance / ($this->gameMetadata['tribes'][$this->data['tribe_id']]['merchants_velocity'] * $this->gameMetadata['game_speed']) * 3600);
                        $newTask->villageId                      = $this->data['selected_village_id'];
                        $newTask->toPlayerId                     = $oRow['player_id'];
                        $newTask->toVillageId                    = $oRow['village_id'];
                        $newTask->procParams                     = $merchantNum . '|' . ($giveResources[1] . ' ' . $giveResources[2] . ' ' . $giveResources[3] . ' ' . $giveResources[4]);
                        $newTask->tag                            = $giveResources;
                        $this->queueModel->addTask($newTask);
                        $newTask              = new QueueTask(QS_MERCHANT_GO, $oRow['player_id'], $oRow['timeInSeconds']);
                        $newTask->villageId   = $oRow['village_id'];
                        $newTask->toPlayerId  = $this->player->playerId;
                        $newTask->toVillageId = $this->data['selected_village_id'];
                        $newTask->procParams  = $oRow['merchants_num'] . '|' . ($needResources[1] . ' ' . $needResources[2] . ' ' . $needResources[3] . ' ' . $needResources[4]);
                        $newTask->tag         = array(
                            '1' => 0,
                            '2' => 0,
                            '3' => 0,
                            '4' => 0
                        );
                        $this->queueModel->addTask($newTask);
                        $m->removeMerchantOffer(intval($_GET['oid']), $oRow['player_id'], $oRow['village_id']);
                        }
                    }
                }
            $this->merchantProperty['showOfferList'] = $showOfferList;
            if ($showOfferList)
                {
                $rowsCount                            = $m->getAllOffersCount($this->data['selected_village_id'], $this->data['rel_x'], $this->data['rel_y'], $this->setupMetadata['map_size'] / 2, $this->gameMetadata['tribes'][$this->data['tribe_id']]['merchants_velocity'] * $this->gameMetadata['game_speed']);
                $this->pageCount                      = (0 < $rowsCount ? ceil($rowsCount / $this->pageSize) : 1);
                $this->pageIndex                      = (((isset($_GET['p']) && is_numeric($_GET['p'])) && intval($_GET['p']) < $this->pageCount) ? intval($_GET['p']) : 0);
                $this->merchantProperty['all_offers'] = $m->getAllOffers($this->data['selected_village_id'], $this->data['rel_x'], $this->data['rel_y'], $this->setupMetadata['map_size'] / 2, $this->gameMetadata['tribes'][$this->data['tribe_id']]['merchants_velocity'] * $this->gameMetadata['game_speed'], $this->pageIndex, $this->pageSize);
                }
            $m->dispose();
            return null;
            }
        if ($this->selectedTabIndex == 2)
            {
            $m                                    = new BuildModel();
            $this->merchantProperty['showError']  = FALSE;
            $this->merchantProperty['showError2'] = FALSE;
            $this->merchantProperty['showError3'] = FALSE;
            if ($this->isPost())
                {
                if ((((((((isset($_POST['m1']) && 0 < intval($_POST['m1'])) && isset($_POST['m2'])) && 0 < intval($_POST['m2'])) && isset($_POST['rid1'])) && 0 < intval($_POST['rid1'])) && isset($_POST['rid2'])) && 0 < intval($_POST['rid2'])))
                    {
                    $resources1 = array(
                        '1' => ((isset($_POST['rid1']) && intval($_POST['rid1']) == 1) ? intval($_POST['m1']) : 0),
                        '2' => ((isset($_POST['rid1']) && intval($_POST['rid1']) == 2) ? intval($_POST['m1']) : 0),
                        '3' => ((isset($_POST['rid1']) && intval($_POST['rid1']) == 3) ? intval($_POST['m1']) : 0),
                        '4' => ((isset($_POST['rid1']) && intval($_POST['rid1']) == 4) ? intval($_POST['m1']) : 0)
                    );
                    $resources2 = array(
                        '1' => ((isset($_POST['rid2']) && intval($_POST['rid2']) == 1) ? intval($_POST['m2']) : 0),
                        '2' => ((isset($_POST['rid2']) && intval($_POST['rid2']) == 2) ? intval($_POST['m2']) : 0),
                        '3' => ((isset($_POST['rid2']) && intval($_POST['rid2']) == 3) ? intval($_POST['m2']) : 0),
                        '4' => ((isset($_POST['rid2']) && intval($_POST['rid2']) == 4) ? intval($_POST['m2']) : 0)
                    );
                    if (((intval($_POST['rid1']) == intval($_POST['rid2']) || intval($resources1[1] + $resources1[2] + $resources1[3] + $resources1[4]) <= 0) || intval($resources2[1] + $resources2[2] + $resources2[3] + $resources2[4]) <= 0))
                        {
                        $this->merchantProperty['showError'] = TRUE;
                        }
                    else
                        {
                        if (10 < ceil(($resources2[1] + $resources2[2] + $resources2[3] + $resources2[4]) / ($resources1[1] + $resources1[2] + $resources1[3] + $resources1[4])))
                            {
                            $this->merchantProperty['showError']  = TRUE;
                            $this->merchantProperty['showError3'] = TRUE;
                            }
                        }
                    $this->merchantProperty['available_res'] = $this->isResourcesAvailable($resources1);
                    if (($this->merchantProperty['available_res'] && !$this->merchantProperty['showError']))
                        {
                        $this->merchantProperty['vRow_merchant_num'] = ceil(($resources1[1] + $resources1[2] + $resources1[3] + $resources1[4]) / $this->merchantProperty['capacity']);
                        if ((0 < $this->merchantProperty['vRow_merchant_num'] && $this->merchantProperty['vRow_merchant_num'] <= $this->merchantProperty['exits_num']))
                            {
                            $this->merchantProperty['exits_num'] -= $this->merchantProperty['vRow_merchant_num'];
                            $this->data['offer_merchants_count'] += $this->merchantProperty['vRow_merchant_num'];
                            $offer = $resources1[1] . ' ' . $resources1[2] . ' ' . $resources1[3] . ' ' . $resources1[4] . '|' . ($resources2[1] . ' ' . $resources2[2] . ' ' . $resources2[3] . ' ' . $resources2[4]);
                            $m->addMerchantOffer($this->player->playerId, $this->data['name'], $this->data['selected_village_id'], $this->data['rel_x'], $this->data['rel_y'], $this->merchantProperty['vRow_merchant_num'], $offer, isset($_POST['ally']), (((isset($_POST['d1']) && isset($_POST['d2'])) && 0 < intval($_POST['d2'])) ? intval($_POST['d2']) : 0), $this->gameMetadata['tribes'][$this->data['tribe_id']]['merchants_velocity'] * $this->gameMetadata['game_speed']);
                            foreach ($resources1 as $k => $v)
                                {
                                $this->resources[$k]['current_value'] -= $v;
                                }
                            $this->queueModel->_updateVillage(FALSE, FALSE);
                            }
                        else
                            {
                            $this->merchantProperty['showError'] = TRUE;
                            }
                        }
                    else
                        {
                        $this->merchantProperty['showError'] = TRUE;
                        }
                    }
                else
                    {
                    $this->merchantProperty['showError']  = TRUE;
                    $this->merchantProperty['showError2'] = TRUE;
                    }
                }
            else
                {
                if ((isset($_GET['d']) && 0 < intval($_GET['d'])))
                    {
                    $row = $m->getOffer(intval($_GET['d']), $this->player->playerId, $this->data['selected_village_id']);
                    if ($row != NULL)
                        {
                        $this->merchantProperty['exits_num'] += $row['merchants_num'];
                        $this->data['offer_merchants_count'] -= $row['merchants_num'];
                        list($resources1, $resources2) = explode('|', $row['offer']);
                        $resourcesArray1 = explode(' ', $resources1);
                        $res             = array();
                        $i               = 0;
                        $_c              = sizeof($resourcesArray1);
                        while ($i < $_c)
                            {
                            $res[$i + 1] = $resourcesArray1[$i];
                            ++$i;
                            }
                        foreach ($res as $k => $v)
                            {
                            $this->resources[$k]['current_value'] += $v;
                            }
                        $this->queueModel->_updateVillage(FALSE, FALSE);
                        $m->removeMerchantOffer(intval($_GET['d']), $this->player->playerId, $this->data['selected_village_id']);
                        }
                    }
                }
            $this->merchantProperty['offers'] = $m->getOffers($this->data['selected_village_id']);
            $m->dispose();
            return null;
            }
        if ($this->selectedTabIndex == 3)
            {
            if ((((($this->isPost() && isset($_POST['m2'])) && is_array($_POST['m2'])) && sizeof($_POST['m2']) == 4) && $this->gameMetadata['plusTable'][6]['cost'] <= $this->data['gold_num']))
                {
                $resources = array(
                    '1' => intval($_POST['m2'][0]),
                    '2' => intval($_POST['m2'][1]),
                    '3' => intval($_POST['m2'][2]),
                    '4' => intval($_POST['m2'][3])
                );
                $oldSum    = $this->resources[1]['current_value'] + $this->resources[2]['current_value'] + $this->resources[3]['current_value'] + $this->resources[4]['current_value'];
                $newSum    = $resources[1] + $resources[2] + $resources[3] + $resources[4];
                if ($newSum <= $oldSum)
                    {
                    foreach ($resources as $k => $v)
                        {
                        $this->resources[$k]['current_value'] = $v;
                        }
                    $this->queueModel->_updateVillage(FALSE, FALSE);
                    $m = new BuildModel();
                    $m->decreaseGoldNum($this->player->playerId, $this->gameMetadata['plusTable'][6]['cost']);
                    $m->dispose();
                    }
                }
            }
        }
    function handleEmbassy()
        {
        if (0 < intval($this->data['alliance_id']))
            {
            return null;
            }
        $this->embassyProperty = array(
            'level' => $this->buildings[$this->buildingIndex]['level'],
            'invites' => NULL,
            'error' => 0,
            'ally1' => '',
            'ally2' => ''
        );
        $maxPlayers            = $this->gameMetadata['items'][18]['levels'][$this->embassyProperty['level'] - 1]['value'];
        if (((($this->isPost() && 3 <= $this->embassyProperty['level']) && isset($_POST['ally1'])) && isset($_POST['ally2'])))
            {
            $this->embassyProperty['ally1'] = $ally1 = trim($_POST['ally1']);
            $this->embassyProperty['ally2'] = $ally2 = trim($_POST['ally2']);
            if (($ally1 == '' || $ally2 == ''))
                {
                $this->embassyProperty['error'] = (($ally1 == '' && $ally2 == '') ? 3 : ($ally1 == '' ? 1 : 2));
                }
            else
                {
                $m = new BuildModel();
                if (!$m->allianceExists($this->embassyProperty['ally1']))
                    {
                    $this->data['alliance_name'] = $this->embassyProperty['ally1'];
                    $this->data['alliance_id']   = $m->createAlliance($this->player->playerId, $this->embassyProperty['ally1'], $this->embassyProperty['ally2'], $maxPlayers);
                    $m->dispose();
                    return null;
                    }
                $this->embassyProperty['error'] = 4;
                $m->dispose();
                }
            }
        $invites_alliance_ids             = trim($this->data['invites_alliance_ids']);
        $this->embassyProperty['invites'] = array();
        if ($invites_alliance_ids != '')
            {
            $_arr = explode('
', $invites_alliance_ids);
            foreach ($_arr as $_s)
                {
                list($allianceId, $allianceName) = explode(' ', $_s, 2);
                $this->embassyProperty['invites'][$allianceId] = $allianceName;
                }
            }
        if (!$this->isPost())
            {
            if ((isset($_GET['a']) && 0 < intval($_GET['a'])))
                {
                $allianceId = intval($_GET['a']);
                if (isset($this->embassyProperty['invites'][$allianceId]))
                    {
                    $m            = new BuildModel();
                    $acceptResult = $m->acceptAllianceJoining($this->player->playerId, $allianceId);
                    if ($acceptResult == 2)
                        {
                        $this->data['alliance_name'] = $this->embassyProperty['invites'][$allianceId];
                        $this->data['alliance_id']   = $allianceId;
                        unset($this->embassyProperty['invites'][$allianceId]);
                        $m->removeAllianceInvites($this->player->playerId, $allianceId);
                        }
                    else
                        {
                        if ($acceptResult == 1)
                            {
                            $this->embassyProperty['error'] = 15;
                            }
                        }
                    $m->dispose();
                    return null;
                    }
                }
            else
                {
                if ((isset($_GET['d']) && 0 < intval($_GET['d'])))
                    {
                    $allianceId = intval($_GET['d']);
                    if (isset($this->embassyProperty['invites'][$allianceId]))
                        {
                        unset($this->embassyProperty['invites'][$allianceId]);
                        $m = new BuildModel();
                        $m->removeAllianceInvites($this->player->playerId, $allianceId);
                        $m->dispose();
                        }
                    }
                }
            }
        }
    function handleWarrior()
        {
        $itemId              = $this->buildings[$this->buildingIndex]['item_id'];
        $this->troopsUpgrade = array();
        $_arr                = explode(',', $this->data['troops_training']);
        foreach ($_arr as $troopStr)
            {
            list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
            if (($researches_done == 1 && 0 < $this->gameMetadata['troops'][$troopId]['gold_needed']))
                {
                $this->troopsUpgrade[$troopId] = $troopId;
                }
            }
        $this->warriorMessage = '';
        if (((($this->isPost() && isset($_POST['tf'])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
            {
            $cropConsume      = 0;
            $totalGoldsNeeded = 0;
            foreach ($_POST['tf'] as $troopId => $num)
                {
                $num = intval($num);
                if (($num <= 0 || !isset($this->troopsUpgrade[$troopId])))
                    {
                    continue;
                    }
                $totalGoldsNeeded += $this->gameMetadata['troops'][$troopId]['gold_needed'] * $num;
                $cropConsume += $this->gameMetadata['troops'][$troopId]['crop_consumption'] * $num;
                }
            if ($totalGoldsNeeded <= 0)
                {
                return null;
                }
            $canProcess           = $totalGoldsNeeded <= $this->data['gold_num'];
            $this->warriorMessage = ($canProcess ? 1 : 2);
            if ($canProcess)
                {
                $troopsString = '';
                foreach ($this->troops as $tid => $num)
                    {
                    if ($tid == 99)
                        {
                        continue;
                        }
                    $neededNum = ((isset($this->troopsUpgrade[$tid]) && isset($_POST['tf'][$tid])) ? $_POST['tf'][$tid] : 0);
                    if ($troopsString != '')
                        {
                        $troopsString .= ',';
                        }
                    $troopsString .= $tid . ' ' . $neededNum;
                    }
                $m = new BuildModel();
                $m->decreaseGoldNum($this->player->playerId, $totalGoldsNeeded);
                $m->dispose();
                $this->data['gold_num'] -= $totalGoldsNeeded;
                $procParams           = $troopsString . '|0||||||1';
                $buildingMetadata     = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']];
                $bLevel               = $this->buildings[$this->buildingIndex]['level'];
                $needed_time          = $buildingMetadata['levels'][$bLevel - 1]['value'] * 3600;
                $newTask              = new QueueTask(QS_WAR_REINFORCE, 0, $needed_time);
                $newTask->villageId   = 0;
                $newTask->toPlayerId  = $this->player->playerId;
                $newTask->toVillageId = $this->data['selected_village_id'];
                $newTask->procParams  = $procParams;
                $newTask->tag         = array(
                    'troops' => NULL,
                    'hasHero' => FALSE,
                    'resources' => NULL,
                    'troopsCropConsume' => $cropConsume
                );
                $this->queueModel->addTask($newTask);
                }
            }
        }
    function handleTroopBuilding()
        {
        $itemId                  = $this->buildings[$this->buildingIndex]['item_id'];
        $this->troopsUpgradeType = QS_TROOP_TRAINING;
        $this->troopsUpgrade     = array();
        $_arr                    = explode(',', $this->data['troops_training']);
        foreach ($_arr as $troopStr)
            {
            list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
            if (($researches_done == 1 && $this->_canTrainInBuilding($troopId, $itemId)))
                {
                $this->troopsUpgrade[$troopId] = $troopId;
                continue;
                }
            }
        if (((($this->isPost() && isset($_POST['tf'])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
            {
            foreach ($_POST['tf'] as $troopId => $num)
                {
                $num = intval($num);
                if ((($num <= 0 || !isset($this->troopsUpgrade[$troopId])) || $this->_getMaxTrainNumber($troopId, $itemId) < $num))
                    {
                    continue;
                    }
                $timeFactor = 1;
                if ($this->gameMetadata['troops'][$troopId]['is_cavalry'] == TRUE)
                    {
                    $flvl = $this->_getMaxBuildingLevel(41);
                    if (0 < $flvl)
                        {
                        $timeFactor -= $this->gameMetadata['items'][41]['levels'][$flvl - 1]['value'] / 100;
                        }
                    }
                $troopMetadata       = $this->gameMetadata['troops'][$troopId];
                $calcConsume         = intval($troopMetadata['training_time_consume'] / $this->gameSpeed * (10 / ($this->buildProperties['building']['level'] + 9)) * $timeFactor);
                $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
                $newTask->threads    = $num;
                $newTask->villageId  = $this->data['selected_village_id'];
                $newTask->buildingId = $this->buildProperties['building']['item_id'];
                $newTask->procParams = $troopId;
                $newTask->tag        = array(
                    '1' => $troopMetadata['training_resources'][1] * $this->buildingTribeFactor * $num,
                    '2' => $troopMetadata['training_resources'][2] * $this->buildingTribeFactor * $num,
                    '3' => $troopMetadata['training_resources'][3] * $this->buildingTribeFactor * $num,
                    '4' => $troopMetadata['training_resources'][4] * $this->buildingTribeFactor * $num
                );
                $this->queueModel->addTask($newTask);
                }
            }
        }
    function handleAcademy()
        {
        $this->troopsUpgradeType = QS_TROOP_RESEARCH;
        $this->troopsUpgrade     = array(
            'available' => array(),
            'soon' => array()
        );
        $_arr                    = explode(',', $this->data['troops_training']);
        foreach ($_arr as $troopStr)
            {
            list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
            if ($researches_done == 0)
                {
                $this->troopsUpgrade[($this->_canDoResearches($troopId) ? 'available' : 'soon')][] = $troopId;
                continue;
                }
            }
        if (((((((isset($_GET['a']) && isset($_GET['k'])) && $_GET['k'] == $this->data['update_key']) && !isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType])) && $this->_canDoResearches(intval($_GET['a']))) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
            {
            $troopId          = intval($_GET['a']);
            $buildingMetadata = $this->gameMetadata['troops'][$troopId];
            if (!$this->isResourcesAvailable($buildingMetadata['research_resources']))
                {
                return null;
                }
            $calcConsume         = intval($buildingMetadata['research_time_consume'] / $this->gameSpeed);
            $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
            $newTask->villageId  = $this->data['selected_village_id'];
            $newTask->procParams = $troopId;
            $newTask->tag        = $buildingMetadata['research_resources'];
            $this->queueModel->addTask($newTask);
            }
        }
    function handleTownHall()
        {
        $buildingMetadata = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']];
        $bLevel           = $this->buildings[$this->buildingIndex]['level'];
        if ((((((isset($_GET['a']) && isset($_GET['k'])) && $_GET['k'] == $this->data['update_key']) && !isset($this->queueModel->tasksInQueue[QS_TOWNHALL_CELEBRATION])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
            {
            if ((((intval($_GET['a']) < 1 || 2 < intval($_GET['a'])) || (intval($_GET['a']) == 1 && $bLevel < $buildingMetadata['celebrations']['small']['level'])) || (intval($_GET['a']) == 2 && $bLevel < $buildingMetadata['celebrations']['large']['level'])))
                {
                return null;
                }
            $key = (intval($_GET['a']) == 2 ? 'large' : 'small');
            if (!$this->isResourcesAvailable($buildingMetadata['celebrations'][$key]['resources']))
                {
                return null;
                }
            $calcConsume         = intval($buildingMetadata['celebrations'][$key]['time_consume'] / $this->gameSpeed * (10 / ($bLevel + 9)));
            $newTask             = new QueueTask(QS_TOWNHALL_CELEBRATION, $this->player->playerId, $calcConsume);
            $newTask->villageId  = $this->data['selected_village_id'];
            $newTask->procParams = intval($_GET['a']);
            $newTask->tag        = $buildingMetadata['celebrations'][$key]['resources'];
            $this->queueModel->addTask($newTask);
            }
        }
    function handleTreasury()
        {
        $buildingMetadata = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']];
        $bLevel           = $this->buildings[$this->buildingIndex]['level'];
        if ((((((isset($_GET['a']) && isset($_GET['k'])) && $_GET['k'] == $this->data['update_key']) && !isset($this->queueModel->tasksInQueue[QS_TOWNHALL_CELEBRATION])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
            {
            if ((((intval($_GET['a']) < 1 || 2 < intval($_GET['a'])) || (intval($_GET['a']) == 1 && $bLevel < $buildingMetadata['celebrations']['small']['level'])) || (intval($_GET['a']) == 2 && $bLevel < $buildingMetadata['celebrations']['large']['level'])))
                {
                return null;
                }
            $key = (intval($_GET['a']) == 2 ? 'large' : 'small');
            if (!$this->isResourcesAvailable($buildingMetadata['celebrations'][$key]['resources']))
                {
                return null;
                }
            $calcConsume         = intval($buildingMetadata['celebrations'][$key]['time_consume'] / $this->gameSpeed * (10 / ($bLevel + 9)));
            $newTask             = new QueueTask(QS_TOWNHALL_CELEBRATION, $this->player->playerId, $calcConsume);
            $newTask->villageId  = $this->data['selected_village_id'];
            $newTask->procParams = intval($_GET['a']);
            $newTask->tag        = $buildingMetadata['celebrations'][$key]['resources'];
            $this->queueModel->addTask($newTask);
            }
        }
    function handleResidencePalace()
        {
        $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 1 <= intval($_GET['t'])) && intval($_GET['t']) <= 3) ? intval($_GET['t']) : 0);
        $_bid_                  = $this->buildings[$this->buildingIndex]['item_id'];
        if ($this->selectedTabIndex == 0)
            {
            if ((((isset($_GET['mc']) && !$this->data['is_capital']) && !$this->data['is_special_village']) && $_bid_ == 26))
                {
                $this->data['is_capital'] = TRUE;
                $m                        = new BuildModel();
                $m->makeVillageAsCapital($this->player->playerId, $this->data['selected_village_id']);
                $m->dispose();
                }
            $this->childVillagesCount = 0;
            if (trim($this->data['child_villages_id']) != '')
                {
                $this->childVillagesCount = sizeof(explode(',', $this->data['child_villages_id']));
                }
            $itemId                  = $this->buildings[$this->buildingIndex]['item_id'];
            $buildingLevel           = $this->buildings[$this->buildingIndex]['level'];
            $this->troopsUpgradeType = QS_TROOP_TRAINING;
            $this->_getOnlyMyTroops();
            $this->troopsUpgrade = array();
            $_arr                = explode(',', $this->data['troops_training']);
            foreach ($_arr as $troopStr)
                {
                list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
                if (($researches_done == 1 && $this->_canTrainInBuilding($troopId, $itemId)))
                    {
                    $this->troopsUpgrade[] = array(
                        'troopId' => $troopId,
                        'maxNumber' => $this->_getMaxTrainNumber($troopId, $itemId),
                        'currentNumber' => $this->_getCurrentNumberFor($troopId, $itemId)
                    );
                    continue;
                    }
                }
            $this->showBuildingForm = FALSE;
            if (((((($buildingLevel < 10 || (2 <= $this->childVillagesCount && $_bid_ == 25)) || (3 <= $this->childVillagesCount && $_bid_ == 26)) || (($this->childVillagesCount == 1 && $buildingLevel < 20) && $_bid_ == 25)) || (($this->childVillagesCount == 1 && $buildingLevel < 15) && $_bid_ == 26)) || (($this->childVillagesCount == 2 && $buildingLevel < 20) && $_bid_ == 26)))
                {
                $this->troopsUpgrade = array();
                }
            else
                {
                if (1 < sizeof($this->troopsUpgrade))
                    {
                    if ((1 <= $this->troopsUpgrade[0]['currentNumber'] || 3 <= $this->troopsUpgrade[1]['currentNumber']))
                        {
                        $this->troopsUpgrade = array();
                        }
                    else
                        {
                        if (0 < $this->troopsUpgrade[1]['currentNumber'])
                            {
                            unset($this->troopsUpgrade[0]);
                            }
                        }
                    }
                else
                    {
                    if (3 <= $this->troopsUpgrade[0]['currentNumber'])
                        {
                        $this->troopsUpgrade = array();
                        }
                    }
                $this->showBuildingForm = 0 < sizeof($this->troopsUpgrade);
                }
            if (((($this->isPost() && isset($_POST['tf'])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
                {
                foreach ($_POST['tf'] as $troopId => $num)
                    {
                    $num         = intval($num);
                    $existsTroop = FALSE;
                    foreach ($this->troopsUpgrade as $troop)
                        {
                        if ($troop['troopId'] == $troopId)
                            {
                            $existsTroop = TRUE;
                            break;
                            }
                        }
                    if ((($num <= 0 || !$existsTroop) || $this->_getMaxTrainNumber($troopId, $itemId) < $num))
                        {
                        continue;
                        }
                    $troopMetadata       = $this->gameMetadata['troops'][$troopId];
                    $calcConsume         = intval($troopMetadata['training_time_consume'] / $this->gameSpeed * (10 / ($this->buildProperties['building']['level'] + 9)));
                    $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
                    $newTask->threads    = $num;
                    $newTask->villageId  = $this->data['selected_village_id'];
                    $newTask->buildingId = $this->buildProperties['building']['item_id'];
                    $newTask->procParams = $troopId;
                    $newTask->tag        = $troopMetadata['training_resources'];
                    $this->queueModel->addTask($newTask);
                    }
                return null;
                }
            }
        else
            {
            if ($this->selectedTabIndex == 1)
                {
                $this->neededCpValue = $this->totalCpRate = $this->totalCpValue = 0;
                $m                   = new BuildModel();
                $result              = $m->getVillagesCp($this->data['villages_id']);
                while ($result->next())
                    {
                    list($this->cpValue, $cpRate) = explode(" ", $result->row['cp']);
                    $this->cpValue += $result->row['elapsedTimeInSeconds'] * ($cpRate / 86400);
                    $this->totalCpRate += $cpRate;
                    $this->totalCpValue += $this->cpValue;
                    $this->neededCpValue += intval($this->gameMetadata['cp_for_new_village'] / $this->gameSpeed);
                    }
                $this->totalCpValue = floor($this->totalCpValue);
                $m->dispose();
                return null;
                }
            if ($this->selectedTabIndex == 3)
                {
                $this->childVillages = array();
                $m                   = new BuildModel();
                $result              = $m->getChildVillagesFor(trim($this->data['child_villages_id']));
                while (($result != NULL && $result->next()))
                    {
                    $this->childVillages[$result->row['id']] = array(
                        'id' => $result->row['id'],
                        'rel_x' => $result->row['rel_x'],
                        'rel_y' => $result->row['rel_y'],
                        'village_name' => $result->row['village_name'],
                        'people_count' => $result->row['people_count'],
                        'creation_date' => $result->row['creation_date']
                    );
                    }
                $m->dispose();
                }
            }
        }
    function handleHerosMansion()
        {
        $this->selectedTabIndex = (((isset($_GET['t']) && is_numeric($_GET['t'])) && intval($_GET['t']) == 1) ? intval($_GET['t']) : 0);
        if ($this->selectedTabIndex == 0)
            {
            $this->hasHero           = 0 < intval($this->data['hero_troop_id']);
            $this->troopsUpgradeType = QS_TROOP_TRAINING_HERO;
            if (!$this->hasHero)
                {
                $this->_getOnlyMyTroops(TRUE);
                if ((((((((isset($_GET['a']) && isset($_GET['k'])) && $_GET['k'] == $this->data['update_key']) && !isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType])) && isset($this->troops[intval($_GET['a'])])) && 0 < $this->troops[intval($_GET['a'])]) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
                    {
                    $troopId       = intval($_GET['a']);
                    $troopMetadata = $this->gameMetadata['troops'][$troopId];
                    $nResources    = array(
                        '1' => $troopMetadata['training_resources'][1] * 2,
                        '2' => $troopMetadata['training_resources'][2] * 2,
                        '3' => $troopMetadata['training_resources'][3] * 2,
                        '4' => $troopMetadata['training_resources'][4] * 2
                    );
                    if (!$this->isResourcesAvailable($nResources))
                        {
                        return null;
                        }
                    $calcConsume         = intval($troopMetadata['training_time_consume'] / $this->gameSpeed * (10 / ($this->buildProperties['building']['level'] + 9))) * 12;
                    $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
                    $newTask->procParams = $troopId . ' ' . $this->data['selected_village_id'];
                    $newTask->tag        = $nResources;
                    $this->queueModel->addTask($newTask);
                    return null;
                    }
                }
            else
                {
                if ((($this->isPost() && isset($_POST['hname'])) && trim($_POST['hname']) != ''))
                    {
                    $this->data['hero_name'] = trim($_POST['hname']);
                    $m                       = new BuildModel();
                    $m->changeHeroName($this->player->playerId, $this->data['hero_name']);
                    $m->dispose();
                    return null;
                    }
                }
            }
        else
            {
            if ($this->selectedTabIndex == 1)
                {
                $this->villageOases = array();
                $m                  = new BuildModel();
                $result             = $m->getVillageOases(trim($this->data['village_oases_id']));
                while (($result != NULL && $result->next()))
                    {
                    $this->villageOases[$result->row['id']] = array(
                        'id' => $result->row['id'],
                        'rel_x' => $result->row['rel_x'],
                        'rel_y' => $result->row['rel_y'],
                        'image_num' => $result->row['image_num'],
                        'allegiance_percent' => $result->row['allegiance_percent']
                    );
                    }
                $m->dispose();
                if (((((((isset($_GET['a']) && isset($_GET['k'])) && $_GET['k'] == $this->data['update_key']) && isset($this->villageOases[intval($_GET['a'])])) && !isset($this->queueModel->tasksInQueue[QS_LEAVEOASIS][intval($_GET['a'])])) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
                    {
                    $oasisId             = intval($_GET['a']);
                    $newTask             = new QueueTask(QS_LEAVEOASIS, $this->player->playerId, floor(21600 / $this->gameSpeed));
                    $newTask->villageId  = $this->data['selected_village_id'];
                    $newTask->buildingId = $oasisId;
                    $newTask->procParams = $this->villageOases[$oasisId]['rel_x'] . ' ' . $this->villageOases[$oasisId]['rel_y'];
                    $this->queueModel->addTask($newTask);
                    return null;
                    }
                if ((isset($_GET['qid']) && 0 < intval($_GET['qid'])))
                    {
                    $this->queueModel->cancelTask($this->player->playerId, intval($_GET['qid']));
                    }
                }
            }
        }
    function preRender()
        {
        parent::prerender();
        if (isset($_GET['p']))
            {
            $this->villagesLinkPostfix .= '&p=' . intval($_GET['p']);
            }
        if (isset($_GET['vid2']))
            {
            $this->villagesLinkPostfix .= '&vid2=' . intval($_GET['vid2']);
            }
        if (0 < $this->selectedTabIndex)
            {
            $this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
            }
        }
    function __getCoordInRange($map_size, $x)
        {
        if ($map_size <= $x)
            {
            $x -= $map_size;
            }
        else
            {
            if ($x < 0)
                {
                $x = $map_size + $x;
                }
            }
        return $x;
        }
    function __getVillageId($map_size, $x, $y)
        {
        return $x * $map_size + ($y + 1);
        }
    function _getOnlyMyOuterTroops()
        {
        $returnTroops = array();
        if (trim($this->data['troops_out_num']) != '')
            {
            $t_arr = explode('|', $this->data['troops_out_num']);
            foreach ($t_arr as $t_str)
                {
                $t2_arr = explode(':', $t_str);
                $t2_arr = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str)
                    {
                    $t = explode(' ', $t2_str);
                    if ($t[1] == 0 - 1)
                        {
                        continue;
                        }
                    if (isset($returnTroops[$t[0]]))
                        {
                        $returnTroops += $t[0] = $t[1];
                        continue;
                        }
                    $returnTroops[$t[0]] = $t[1];
                    }
                }
            }
        if (trim($this->data['troops_out_intrap_num']) != '')
            {
            $t_arr = explode('|', $this->data['troops_out_intrap_num']);
            foreach ($t_arr as $t_str)
                {
                $t2_arr = explode(':', $t_str);
                $t2_arr = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str)
                    {
                    $t = explode(' ', $t2_str);
                    if ($t[1] == 0 - 1)
                        {
                        continue;
                        }
                    if (isset($returnTroops[$t[0]]))
                        {
                        $returnTroops += $t[0] = $t[1];
                        continue;
                        }
                    $returnTroops[$t[0]] = $t[1];
                    }
                }
            }
        return $returnTroops;
        }
    function _getOnlyMyTroops($toBeHero = FALSE)
        {
        $t_arr = explode('|', $this->data['troops_num']);
        foreach ($t_arr as $t_str)
            {
            $t2_arr = explode(':', $t_str);
            if ($t2_arr[0] == 0 - 1)
                {
                $t2_arr = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str)
                    {
                    $t = explode(' ', $t2_str);
                    if (($toBeHero && (((((((((((((((((((($t[0] == 99 || $t[0] == 7) || $t[0] == 8) || $t[0] == 9) || $t[0] == 10) || $t[0] == 17) || $t[0] == 18) || $t[0] == 19) || $t[0] == 20) || $t[0] == 27) || $t[0] == 28) || $t[0] == 29) || $t[0] == 30) || $t[0] == 106) || $t[0] == 107) || $t[0] == 108) || $t[0] == 109) || $t[0] == 57) || $t[0] == 58) || $t[0] == 59) || $t[0] == 60)))
                        {
                        continue;
                        }
                    if (isset($this->troops[$t[0]]))
                        {
                        $this->troops += $t[0] = $t[1];
                        continue;
                        }
                    $this->troops[$t[0]] = $t[1];
                    }
                continue;
                }
            }
        if ((!$toBeHero && !isset($this->troops[99])))
            {
            $this->troops[99] = 0;
            }
        }
    function _getMaxBuildingLevel($itemId)
        {
        $result = 0;
        foreach ($this->buildings as $villageBuild)
            {
            if (($villageBuild['item_id'] == $itemId && $result < $villageBuild['level']))
                {
                $result = $villageBuild['level'];
                continue;
                }
            }
        return $result;
        }
    function _getCurrentNumberFor($troopId, $item)
        {
        $num = 0;
        if (isset($this->troops[$troopId]))
            {
            $num += $this->troops[$troopId];
            }
        if ((isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType]) && isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType][$item])))
            {
            $qts = $this->queueModel->tasksInQueue[$this->troopsUpgradeType][$item];
            foreach ($qts as $qt)
                {
                if ($qt['proc_params'] == $troopId)
                    {
                    $num += $qt['threads'];
                    continue;
                    }
                }
            }
        $num += $this->_getTroopCountInTransfer($troopId, QS_WAR_REINFORCE);
        $num += $this->_getTroopCountInTransfer($troopId, QS_WAR_ATTACK);
        $num += $this->_getTroopCountInTransfer($troopId, QS_WAR_ATTACK_PLUNDER);
        $num += $this->_getTroopCountInTransfer($troopId, QS_WAR_ATTACK_SPY);
        $num += $this->_getTroopCountInTransfer($troopId, QS_CREATEVILLAGE);
        $ts = $this->_getOnlyMyOuterTroops();
        if (isset($ts[$troopId]))
            {
            $num += $ts[$troopId];
            }
        return $num;
        }
    function _getTroopCountInTransfer($troopId, $type)
        {
        $num = 0;
        if (isset($this->queueModel->tasksInQueue[$type]))
            {
            $qts = $this->queueModel->tasksInQueue[$type];
            foreach ($qts as $qt)
                {
                $arr = explode('|', $qt['proc_params']);
                $arr = explode(',', $arr[0]);
                foreach ($arr as $arrStr)
                    {
                    list($tid, $tnum) = explode(' ', $arrStr);
                    if ($tid == $troopId)
                        {
                        $num += $tnum;
                        continue;
                        }
                    }
                }
            }
        return $num;
        }
    function _getMaxTrainNumber($troopId, $item)
        {
        $max = 0;
        $_f  = TRUE;
        foreach ($this->gameMetadata['troops'][$troopId]['training_resources'] as $k => $v)
            {
            $num = floor($this->resources[$k]['current_value'] / ($v * $this->buildingTribeFactor));
            if (($num < $max || $_f))
                {
                $_f  = FALSE;
                $max = $num;
                continue;
                }
            }
        if ($troopId == 99)
            {
            $buildingMetadata = $this->gameMetadata['items'][$this->buildings[$this->buildingIndex]['item_id']]['levels'][$this->buildProperties['building']['level'] - 1];
            $_maxValue        = $buildingMetadata['value'] - $this->troops[$troopId];
            if ((isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType]) && isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType][$this->buildProperties['building']['item_id']])))
                {
                $qts = $this->queueModel->tasksInQueue[$this->troopsUpgradeType][$this->buildProperties['building']['item_id']];
                foreach ($qts as $qt)
                    {
                    if ($qt['proc_params'] == $troopId)
                        {
                        $_maxValue -= $qt['threads'];
                        continue;
                        }
                    }
                }
            if ($_maxValue < $max)
                {
                $max = $_maxValue;
                }
            }
        else
            {
            if (($item == 25 || $item == 26))
                {
                $_maxValue = ((((($troopId == 9 || $troopId == 19) || $troopId == 29) || $troopId == 108) || $troopId == 59) ? 1 : 3);
                $_maxValue -= $this->_getCurrentNumberFor($troopId, $item);
                if ($_maxValue < $max)
                    {
                    $max = $_maxValue;
                    }
                }
            }
        return ($max < 0 ? 0 : $max);
        }
    function _canTrainInBuilding($troopId, $itemId)
        {
        foreach ($this->gameMetadata['troops'][$troopId]['trainer_building'] as $buildingId)
            {
            if ($buildingId == $itemId)
                {
                return TRUE;
                }
            }
        return FALSE;
        }
    function _canDoResearches($troopId)
        {
        foreach ($this->gameMetadata['troops'][$troopId]['pre_requests'] as $req_item_id => $level)
            {
            $result = FALSE;
            foreach ($this->buildings as $villageBuild)
                {
                if (($villageBuild['item_id'] == $req_item_id && $level <= $villageBuild['level']))
                    {
                    $result = TRUE;
                    break;
                    continue;
                    }
                }
            if (!$result)
                {
                return FALSE;
                }
            }
        return TRUE;
        }
    function getNeededTime($neededResources)
        {
        $timeInSeconds = 0;
        foreach ($neededResources as $k => $v)
            {
            if ($this->resources[$k]['current_value'] < $v)
                {
                if ($this->resources[$k]['calc_prod_rate'] <= 0)
                    {
                    return 0 - 1;
                    }
                $time = ($v - $this->resources[$k]['current_value']) / $this->resources[$k]['calc_prod_rate'];
                if ($timeInSeconds < $time)
                    {
                    $timeInSeconds = $time;
                    continue;
                    }
                continue;
                }
            }
        return ceil($timeInSeconds * 3600);
        }
    function getActionText4($neededResources, $url, $text, $queueTaskType, $buildLevel, $troopLevel)
        {
        if (isset($this->queueModel->tasksInQueue[$queueTaskType]))
            {
            return '<span class="none">' . buildings_p_plwait . '</span>';
            }
        if ($buildLevel <= $troopLevel)
            {
            return '<span class="none">' . buildings_p_needmorecapacity . '</span>';
            }
        return (!$this->isResourcesAvailable($neededResources) ? '<span class="none">' . buildings_p_notenoughres . '</span>' : '<a class="build" href="build.php?id=' . $this->buildingIndex . '&' . $url . '&k=' . $this->data['update_key'] . '">' . $text . '</a>');
        }
    function getActionText3($neededResources, $url, $text, $queueTaskType)
        {
        if (isset($this->queueModel->tasksInQueue[$queueTaskType]))
            {
            return '<span class="none">' . buildings_p_plwait . '</span>';
            }
        return (!$this->isResourcesAvailable($neededResources) ? '<span class="none">' . buildings_p_notenoughres . '</span>' : '<a class="build" href="build.php?id=' . $this->buildingIndex . '&' . $url . '&k=' . $this->data['update_key'] . '">' . $text . '</a>');
        }
    function getActionText2($neededResources)
        {
        $needUpgradeType = $this->needMoreUpgrades($neededResources);
        if (0 < $needUpgradeType)
            {
            switch ($needUpgradeType)
            {
                case 2:
                    return '<span class="none">' . buildings_p_upg1 . '</span>';
                case 3:
                    return '<span class="none">' . buildings_p_upg2 . '</span>';
                case 4:
                    return '<span class="none">' . buildings_p_upg3 . '</span>';
            }
            }
        if (!$this->isResourcesAvailable($neededResources))
            {
            $neededTime = $this->getNeededTime($neededResources);
            return '<span class="none">' . (0 < $neededTime ? buildings_p_willenoughresat . ' ' . WebHelper::secondstostring($neededTime) . ' ' . time_hour_lang : buildings_p_notenoughres2) . '</span>';
            }
        return '';
        }
    function getActionText($neededResources, $isField, $upgrade, $item_id)
        {
        $needUpgradeType = $this->needMoreUpgrades($neededResources, $item_id);
        if (0 < $needUpgradeType)
            {
            switch ($needUpgradeType)
            {
                case 1:
                    return '<span class="none">' . buildings_p_upg0 . '</span>';
                case 2:
                    return '<span class="none">' . buildings_p_upg1 . '</span>';
                case 3:
                    return '<span class="none">' . buildings_p_upg2 . '</span>';
                case 4:
                    return '<span class="none">' . buildings_p_upg3 . '</span>';
            }
            }
        else
            {
            if ($this->isResourcesAvailable($neededResources))
                {
                $pageNamePostfix = ($isField ? '1' : '2');
                $link            = ($upgrade ? '<a class="build" href="village' . $pageNamePostfix . '.php?id=' . $this->buildingIndex . '&k=' . $this->data['update_key'] . '">' . buildings_p_upg_tolevel . ' ' . $this->buildProperties['nextLevel'] . '</a>' : '<a class="build" href="village2.php?id=' . $this->buildingIndex . '&b=' . $item_id . '&k=' . $this->data['update_key'] . '">' . buildings_p_create_newbuild . '</a>');
                $workerResult    = $this->isWorkerBusy($isField);
                return ($workerResult['isBusy'] ? '<span class="none">' . buildings_p_workersbusy . '</span>' : $link . ($workerResult['isPlusUsed'] ? ' <span class="none">(' . buildings_p_wait_buildqueue . ')</span>' : ''));
                }
            }
        $neededTime = $this->getNeededTime($neededResources);
        return '<span class="none">' . (0 < $neededTime ? buildings_p_willenoughresat . ' ' . WebHelper::secondstostring($neededTime) . ' ' . time_hour_lang : buildings_p_notenoughres2) . '</span>';
        }
    function _canAcceptOffer($needResources, $giveResources, $villageId, $onlyForAlliance, $allianceId, $maxTime, $distance)
        {
        if ($villageId == $this->data['selected_village_id'])
            {
            return 0;
            }
        if (!$this->isResourcesAvailable($giveResources))
            {
            return 1;
            }
        $needMerchantCount = ceil(($giveResources[1] + $giveResources[2] + $giveResources[3] + $giveResources[4]) / $this->merchantProperty['capacity']);
        if (($needMerchantCount == 0 || $this->merchantProperty['exits_num'] < $needMerchantCount))
            {
            return 2;
            }
        if (($onlyForAlliance && (intval($this->data['alliance_id']) == 0 || $allianceId != intval($this->data['alliance_id']))))
            {
            return 3;
            }
        if ((0 < $maxTime && $maxTime < $distance / $this->merchantProperty['speed']))
            {
            return 4;
            }
        return 5;
        }
    function getNextLink()
        {
        $text = '»';
        if ($this->pageIndex + 1 == $this->pageCount)
            {
            return $text;
            }
        $link = '';
        if (0 < $this->selectedTabIndex)
            {
            $link .= 't=' . $this->selectedTabIndex;
            }
        if ($link != '')
            {
            $link .= '&';
            }
        $link .= 'p=' . ($this->pageIndex + 1);
        $link = 'build.php?id=' . $this->buildingIndex . '&' . $link;
        return '<a href="' . $link . '">' . $text . '</a>';
        }
    function getPreviousLink()
        {
        $text = '«';
        if ($this->pageIndex == 0)
            {
            return $text;
            }
        $link = '';
        if (0 < $this->selectedTabIndex)
            {
            $link .= 't=' . $this->selectedTabIndex;
            }
        if (1 < $this->pageIndex)
            {
            if ($link != '')
                {
                $link .= '&';
                }
            $link .= 'p=' . ($this->pageIndex - 1);
            }
        $link = 'build.php?id=' . $this->buildingIndex . '&' . $link;
        return '<a href="' . $link . '">' . $text . '</a>';
        }
    function getResourceGoldExchange($neededResources, $itemId, $buildingIndex, $multiple = FALSE)
        {
        if ((($this->data['gold_num'] < $this->gameMetadata['plusTable'][6]['cost'] || 0 < $this->needMoreUpgrades($neededResources, $itemId)) || ($this->isResourcesAvailable($neededResources) && !$multiple)))
            {
            return '';
            }
        $s1               = 0;
        $s2               = 0;
        $exchangeResource = '';
        foreach ($neededResources as $k => $v)
            {
            $s1 += $v;
            $s2 += $this->resources[$k]['current_value'];
            if ($exchangeResource != '')
                {
                $exchangeResource .= '&';
                }
            $exchangeResource .= 'r' . $k . '=' . $v;
            }
        $canExchange = $s1 <= $s2;
        if (($multiple && $canExchange))
            {
            $num              = floor($s2 / $s1);
            $exchangeResource = '';
            foreach ($neededResources as $k => $v)
                {
                if ($exchangeResource != '')
                    {
                    $exchangeResource .= '&';
                    }
                $exchangeResource .= 'r' . $k . '=' . $v * $num;
                }
            }
        return ' | <a href="build.php?bid=17&t=3&rid=' . $buildingIndex . '&' . $exchangeResource . '" title="' . buildings_p_m2m . '"><img class="npc' . ($canExchange ? '' : '_inactive') . '" src="assets/x.gif" alt="' . buildings_p_m2m . '" title="' . buildings_p_m2m . '"></a>';
        }
    }
$p = new GPage();
$p->run();
?>