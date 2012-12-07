<?php
require('.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php');
require_once(MODEL_PATH . 'alliance.php');
class GPage extends SecureGamePage
    {
    var $selectedTabIndex = null;
    var $fullView = null;
    var $hasAlliance = FALSE;
    var $allianceData = NULL;
    var $lastReports = NULL;
    var $hasErrors = FALSE;
    var $invitesResult = -1;
    var $contracts = null;
    var $bbCodeReplacedArray = array();
    function GPage()
        {
        parent::securegamepage();
        $this->viewFile        = 'alliance.phtml';
        $this->contentCssClass = 'alliance';
        }
    function load()
        {
        parent::load();
        $m                  = new AllianceModel();
        $allianceId         = 0;
        $this->allianceData = NULL;
        if ((isset($_GET['id']) && 0 < intval($_GET['id'])))
            {
            $allianceId         = intval($_GET['id']);
            $this->allianceData = $m->getAllianceData($allianceId);
            }
        if ($this->allianceData == NULL)
            {
            $allianceId = intval($this->data['alliance_id']);
            if ($allianceId <= 0)
                {
                $this->hasAlliance = FALSE;
                return null;
                }
            $this->allianceData = $m->getAllianceData($allianceId);
            }
        $this->hasAlliance      = TRUE;
        $this->fullView         = $allianceId == intval($this->data['alliance_id']);
        $this->selectedTabIndex = 0;
        if ($this->fullView)
            {
            $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 0 <= intval($_GET['t'])) && intval($_GET['t']) <= 3) ? intval($_GET['t']) : 0);
            if (($this->selectedTabIndex == 1 && !$this->hasAllianceEditRole()))
                {
                $this->selectedTabIndex = 0;
                }
            }
        if ($this->isPost())
            {
            if ((($this->fullView && $this->selectedTabIndex == 1) && $this->hasAllianceEditRole()))
                {
                $newData = array(
                    'name' => ((isset($_POST['aname1']) && trim($_POST['aname1']) != '') ? $_POST['aname1'] : $this->allianceData['name']),
                    'name2' => ((isset($_POST['aname2']) && trim($_POST['aname2']) != '') ? $_POST['aname2'] : $this->allianceData['name2']),
                    'description1' => htmlspecialchars($_POST['be1']),
                    'description2' => htmlspecialchars($_POST['be2'])
                );
                $m->editAllianceData(intval($this->data['alliance_id']), $newData, $this->allianceData['players_ids']);
                $m->dispose();
                $this->redirect('alliance.php');
                return null;
                }
            }
        if (((((($this->selectedTabIndex == 0 && isset($_GET['d'])) && 0 < intval($_GET['d'])) && $this->hasAllianceRemovePlayerRole()) && $this->player->playerId != intval($_GET['d'])) && $this->isMemberOfAlliance(intval($_GET['d']))))
            {
            $this->allianceData['players_ids'] = $m->removeFromAlliance(intval($_GET['d']), $allianceId, $this->allianceData['players_ids'], $this->allianceData['player_count']);
            --$this->allianceData['player_count'];
            }
        else
            {
            if ($this->selectedTabIndex == 2)
                {
                $lastReportsType = 0;
                if (isset($_GET['ac']))
                    {
                    if ($_GET['ac'] == 1)
                        {
                        $lastReportsType = 1;
                        }
                    else if ($_GET['ac'] == 2)
                        {
                        $lastReportsType = 2;
                        }
                    }
                $this->lastReports = $m->getLatestReports($this->allianceData['players_ids'], $lastReportsType);
                }
            else
                {
                if ($this->selectedTabIndex == 3)
                    {
                    if (isset($_GET['a']))
                        {
                        switch ($_GET['a'])
                        {
                            case 1:
                                if (!$this->hasAllianceInviteRoles())
                                    {
                                    unset($_GET['a']);
                                    break;
                                    }
                                $this->allianceData['players_invites'] = array();
                                if (trim($this->allianceData['invites_player_ids']) != '')
                                    {
                                    $invites = explode('', trim($this->allianceData['invites_player_ids']));
                                    foreach ($invites as $invite)
                                        {
                                        list($pid, $pname) = explode(' ', $invite, 2);
                                        $this->allianceData['players_invites'][$pid] = $pname;
                                        }
                                    }
                                if (($this->isPost() && isset($_POST['a_name'])))
                                    {
                                    $pid = intval($m->getPlayerId($_POST['a_name']));
                                    if (0 < $pid)
                                        {
                                        if (!isset($this->allianceData['players_invites'][$pid]))
                                            {
                                            $this->invitesResult                         = 2;
                                            $this->allianceData['players_invites'][$pid] = $_POST['a_name'];
                                            $m->addAllianceInvites($pid, $allianceId);
                                            }
                                        }
                                    else
                                        {
                                        $this->invitesResult = 1;
                                        }
                                    }
                                if (((isset($_GET['d']) && 0 < intval($_GET['d'])) && isset($this->allianceData['players_invites'][intval($_GET['d'])])))
                                    {
                                    unset($this->allianceData['players_invites'][intval($_GET['d'])]);
                                    $m->removeAllianceInvites(intval($_GET['d']), $allianceId);
                                    }
                                break;
                            case 2:
                                if (!$this->hasAllianceEditContractRole())
                                    {
                                    unset($_GET['a']);
                                    break;
                                    }
                                $contracts_alliance_id = trim($this->allianceData['contracts_alliance_id']);
                                $contracts             = array();
                                if ($contracts_alliance_id != '')
                                    {
                                    $contracts_alliance_idArr = explode(',', $contracts_alliance_id);
                                    foreach ($contracts_alliance_idArr as $item)
                                        {
                                        list($aid, $pendingStatus) = explode(' ', $item);
                                        $contracts[$aid] = $pendingStatus;
                                        }
                                    }
                                $this->hasErrors = TRUE;
                                if (!$this->isPost())
                                    {
                                    if (((isset($_GET['d']) && 0 < intval($_GET['d'])) && isset($contracts[$_GET['d']])))
                                        {
                                        unset($contracts[$_GET['d']]);
                                        $m->removeAllianceContracts($allianceId, intval($_GET['d']));
                                        }
                                    if (((isset($_GET['c']) && 0 < intval($_GET['c'])) && isset($contracts[$_GET['c']])))
                                        {
                                        $contracts[$_GET['c']] = 0;
                                        $m->acceptAllianceContracts($allianceId, intval($_GET['c']));
                                        }
                                    }
                                else if ((isset($_POST['a_name']) && trim($_POST['a_name']) != ''))
                                    {
                                    $caid = intval($m->getAllianceId(trim($_POST['a_name'])));
                                    if ((0 < $caid && !isset($contracts[$caid])))
                                        {
                                        $m->addAllianceContracts($allianceId, $caid);
                                        $contracts[$caid] = 1;
                                        $this->hasErrors  = FALSE;
                                        }
                                    }
                                $this->contracts = $contracts;
                                break;
                            case 3:
                                if ($this->isPost())
                                    {
                                    if ((isset($_POST['pw']) && strtolower($this->data['pwd']) == strtolower(md5($_POST['pw']))))
                                        {
                                        $this->allianceData['players_ids'] = $m->removeFromAlliance($this->player->playerId, $allianceId, $this->allianceData['players_ids'], $this->allianceData['player_count']);
                                        --$this->allianceData['player_count'];
                                        $m->dispose();
                                        $this->redirect('alliance.php');
                                        return null;
                                        }
                                    $this->hasErrors = TRUE;
                                    }
                        }
                        }
                    }
                }
            }
        if ($this->selectedTabIndex == 0)
            {
            $contracts_alliance_id = trim($this->allianceData['contracts_alliance_id']);
            $this->contracts       = array();
            if ($contracts_alliance_id != '')
                {
                $contracts_alliance_idArr = explode(',', $contracts_alliance_id);
                foreach ($contracts_alliance_idArr as $item)
                    {
                    list($aid, $pendingStatus) = explode(' ', $item);
                    if ($pendingStatus == 0)
                        {
                        $this->contracts[$aid] = $m->getAllianceName($aid);
                        }
                    }
                }
            $this->allianceData['rank']    = $m->getAllianceRank($allianceId, $this->allianceData['score']);
            $result                        = $m->getAlliancePlayers($this->allianceData['players_ids']);
            $this->allianceData['players'] = array();
            while (($result != NULL && $result->next()))
                {
                $this->allianceData['players'][] = array(
                    'id' => $result->row['id'],
                    'name' => $result->row['name'],
                    'total_people_count' => $result->row['total_people_count'],
                    'alliance_roles' => $result->row['alliance_roles'],
                    'villages_count' => $result->row['villages_count'],
                    'lastLoginFromHours' => $result->row['lastLoginFromHours']
                );
                }
            }
        $m->dispose();
        }
    function _hasAllianceRole($role)
        {
        $alliance_roles = trim($this->data['alliance_roles']);
        if ($alliance_roles == '')
            {
            return FALSE;
            }
        list($roleNumber, $roleName) = explode(' ', $alliance_roles, 2);
        return $roleNumber & $role;
        }
    function hasAllianceEditRole()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_EDITNAMES);
        }
    function hasAllianceRemovePlayerRole()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_REMOVEPLAYER);
        }
    function hasAllianceSetRoles()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_SETROLES);
        }
    function hasAllianceInviteRoles()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_INVITEPLAYERS);
        }
    function hasAllianceEditContractRole()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_EDITCONTRACTS);
        }
    function preRender()
        {
        parent::prerender();
        if (isset($_GET['id']))
            {
            $this->villagesLinkPostfix .= '&id=' . intval($_GET['id']);
            }
        if (0 < $this->selectedTabIndex)
            {
            $this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
            }
        }
    function getAllianceName($aid)
        {
        $m = new AllianceModel();
        $n = $m->getAllianceName($aid);
        return (trim($n) != '' ? $n : '[?]');
        }
    function getAllianceDataFor($playerId)
        {
        $m = new AllianceModel();
        return $m->getAllianceDataFor($playerId);
        }
    function isMemberOfAlliance($playerId)
        {
        $players_ids = trim($this->allianceData['players_ids']);
        if ($players_ids == '')
            {
            return FALSE;
            }
        $arr = explode(',', $players_ids);
        foreach ($arr as $pid)
            {
            if ($pid == $playerId)
                {
                return TRUE;
                }
            }
        return FALSE;
        }
    function getOnlineStatus($lastLoginFromHours)
        {
        if ($lastLoginFromHours <= 1)
            {
            return '<img class="online1" src="assets/x.gif" title="' . alliance_p_status1 . '" alt="' . alliance_p_status1 . '">';
            }
        if ($lastLoginFromHours <= 24)
            {
            return '<img class="online2" src="assets/x.gif" title="' . alliance_p_status2 . '" alt="' . alliance_p_status2 . '">';
            }
        if ($lastLoginFromHours <= 24 * 3)
            {
            return '<img class="online3" src="assets/x.gif" title="' . alliance_p_status3 . '" alt="' . alliance_p_status3 . '">';
            }
        if ($lastLoginFromHours <= 24 * 7)
            {
            return '<img class="online4" src="assets/x.gif" title="' . alliance_p_status4 . '" alt="' . alliance_p_status4 . '">';
            }
        return '<img class="online5" src="assets/x.gif" title="' . alliance_p_status5 . '" alt="' . alliance_p_status5 . '">';
        }
    function getAllianceDescription($text)
        {
        $img    = '<img class="%s" src="assets/x.gif" onmouseout="med_closeDescription()" onmousemove="med_mouseMoveHandler(arguments[0],\'<p>%s</p>\')">';
        $medals = explode(',', $this->allianceData['medals']);
        foreach ($medals as $medal)
            {
            if (trim($medal) == '')
                {
                continue;
                }
            list($index, $rank, $week) = explode(':', $medal);
            if (!isset($this->gameMetadata['medals'][$index]))
                {
                continue;
                }
            $medalData = $this->gameMetadata['medals'][$index];
            $bbCode    = intval($medalData['BBCode']) + intval($week) * 10 + (intval($rank) - 1);
            $cssClass  = 'medal ' . $medalData['cssClass'] . '_' . $rank;
            $altText   = htmlspecialchars(sprintf('<table><tr><th>' . profile_medal_txt_cat . ':</th><td>%s</td></tr><tr><th>' . profile_medal_txt_week . ':</th><td>%s</td></tr><tr><th>' . profile_medal_txt_rank . ':</th><td>%s</td></tr></table>', constant('medal_' . $medalData['textIndex']), $week, $rank));
            if (!isset($this->bbCodeReplacedArray[$bbCode]))
                {
                $count = 0;
                $text  = preg_replace('/\[#' . $bbCode . '\]/', sprintf($img, $cssClass, $altText), $text, 1, $count);
                if (0 < $count)
                    {
                    $this->bbCodeReplacedArray[$bbCode] = $count;
                    }
                }
            }
        $contractsStr = '';
        foreach ($this->contracts as $aid => $aname)
            {
            $contractsStr .= '<a href="alliance.php?id=' . $aid . '">' . $aname . '</a><br/>';
            }
        if (!isset($this->bbCodeReplacedArray['contracts']))
            {
            $count = 0;
            $text  = preg_replace('/\[contracts\]/', $contractsStr, $text, 1, $count);
            if (0 < $count)
                {
                $this->bbCodeReplacedArray['contracts'] = $count;
                }
            }
        return nl2br($text);
        }
    }
$p = new GPage();
$p->run();
?>