<?php

class genshinSlip_actions extends app
{
  function __construct(&$appManager)
  {
    $appManager->register('plugin', $this, 'EventFun');
    $this->linkRedis();
  }

  function EventFun($msg)
  {
    $msgPort = $msg['Port'];
    $msgPid = $msg['Pid'];
    $msgVer = $msg['Ver'];
    $msgId = $msg['MsgID'];
    $msgRobot = $msg['Robot'];
    $msgType = $msg['MsgType'];
    $msgSubType = $msg['MsgSubType'];
    $msgSource = $msg['Source'];
    $msgSender = $msg['Sender'];
    $msgReceiver = $msg['Receiver'];
    $msgContent = base64_decode($msg['Content']);
    $msgOrigMsg = base64_decode($msg['OrigMsg']);

    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
    $msgContent = str_replace(" ", "", $msgContent);

    if (preg_match("/^(御神签|求签|slip)$/i", $msgContent, $msgMatch)) {
      $ret = $this->getGenshinSlip($msgSender);
    }

    $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
  }

  /**
   * 
   * 抽取御神签
   * 
   */
  function getGenshinSlip($msgSender)
  {

    $slip = array(
      "4oCU4oCU5aSn5ZCJ4oCU4oCUCuWuneWJkeWHuuWMo+adpe+8jOaXoOW+gOS4jeWIqeOAguWHuuWMo+S5i+WFie+8jOS6puiDveeFp+S6ruS7luS6uuOAggrku4rml6Xog73kuIDnrq3lsITkuK3nqbrkuK3nmoTnjI7nianvvIzog73kuIDlh7vlkb3kuK3lrojljavopoHlrrPjgIIK6Iul5rKh5pyJ55uu5qCH77yM5LiN5aao5Zub5aSE6L2s6L2s77yM6K+05LiN5a6a5Lya5pyJ5oSP5aSW5LmL5Zac44CCCuWQjOaXtu+8jOS5n+S4jeimgeW/mOiusOWSjOWAkumcieeahOWQjOS8tOWIhuS6q+S4gOS4i+Wlvei/kOawlOWTpuOAggoK5LuK5aSp55qE5bm46L+Q54mp5pivOumavuW+l+S4gOingeeahOOAjOmprOWwvuOAjeOAggrpqazlsL7pmo/lpKfniYfojbvojYnnlJ/plb/vvIzkvYbljbTmm7TkuLrmjLrmi5TjgIIK5LiO5YKy54S25oy656uL5LqO5q2k5LiW55qE5L2g5LiA5a6a5b6I5piv55u46YWN44CC",
      "4oCU4oCU5aSn5ZCJ4oCU4oCUCuWkseiAjOWkjeW+l+eahOS4gOWkqeOAggrljp/mnKzku6XkuLrnn7PmsonlpKfmtbfnmoTkuovmg4XmnInkuoblpb3nmoTlm57lupTvvIwK5Y6f5pys5YiG6YGT5oms6ZWz55qE5pyL5Y+L5oiW6K645Y+v5Lul5YaN5bqm5ZKM5aW977yMCuS4jee7j+aEj+mXtOaDs+i1t+S6huWOn+acrOW3sue7j+W/mOiusOS6hueahOS6i+aDheOAggrkuJbnlYzkuIrmsqHmnInku4DkuYjmmK/msLjov5zml6Dms5XmjL3lm57nmoTvvIwK5LuK5aSp5bCx5piv6IO95aSf5oy95Zue5aSx5Y675LqL54mp55qE5pel5a2Q44CCCgrku4rlpKnnmoTlubjov5DnianmmK865rS76Lmm5Lmx6Lez55qE44CM6ay85YWc6Jmr44CN44CCCumsvOWFnOiZq+aYr+eIseWlveWSjOW5s+OAgeS4jeaEv+aEj+S6ieaWl+eahOWwj+eUn+eJqeOAggrov5nku73ov73msYLlubPlkoznmoTlv4PkuIDlrprog73kuLrkvaDluKbmnaXlubjnpo/lkKfjgII=",
      "4oCU4oCU5aSn5ZCJ4oCU4oCUCuS8mui1t+mjjueahOaXpeWtkO+8jOaXoOiuuuW5suS7gOS5iOmDveS8muW+iOmhuuWIqeeahOS4gOWkqeOAggrlkajlm7TnmoTkurrlv4Pmg4XkuZ/pnZ7luLjmhInlv6vvvIznu53lr7nkuI3kvJrlj5HnlJ/lhrLnqoHvvIwK6L+Y5Y+v5Lul5ZCD5Yiw5LiA55u05oOz5ZCD77yM5L2G5rKh5py65Lya5ZCD55qE576O5ZGz5L2z6IK044CCCuaXoOiuuuaYr+W3peS9nO+8jOi/mOaYr+aXheihjO+8jOmDveS4gOWumuS8muWNgeWIhumhuuWIqeWQp+OAggrpgqPkuYjvvIzlupTlvZPlnKjov5nmoLfnmoTlpb3ml7bovrDph4zvvIzkuIDpvJPkvZzmsJTliY3ov5suLi4KCuS7iuWkqeeahOW5uOi/kOeJqeaYrzrojIHlo67miJDplb/nmoTjgIzpuKPojYnjgI3jgIIK6K645aSa5Lq65oiW6K645LiN55+l6YGT77yM6bij6I2J5piv6IO96aKE5oql6Zu35pq055qE5qSN54mp44CCCuWQkeW+gOedgOmbt+elnuWkp+S6uueahOmdkuedkO+8jOWPquWcqOeou+Wmu+WIl+Wym+S4iueUn+mVv+OAggrmkZjkuIvpuKPojYnml7bphaXphaXpurvpurvnmoTop6bmhJ/vvIzmja7or7Tlkozlubjnpo/nmoTmu4vlkbPlvojlg4/jgII=",
      "4oCU4oCU5aSn5ZCJ4oCU4oCUCua1ruS6keaVo+WwveaciOW9k+epuu+8jOmAouatpOetvuiAheeahuS4uuS4iuWQieOAggrmmI7plZzlnKjlv4PmuIXlpoLorrjvvIzmiYDmsYLkuYvkuovlv4Pmg7PliJnmiJDjgIIK5ZCI6YCC6aG65b+D6ICM5Li655qE5LiA5aSp77yM5LiN566h5piv5oOz5YGa55qE5LqL5oOF77yMCui/mOaYr+aDs+ingeeahOS6uu+8jOeOsOWcqOaYr+ihjOWKqOi1t+adpeeahOWlveaXtuacuuOAggoK5LuK5aSp55qE5bm46L+Q54mp5pivOuS4jeaWreWPkeeDreeahOOAjOeDiOeEsOiKseiKseiViuOAjeOAggrng4jnhLDoirHnmoTngpnng63mnaXoh6rkuo7ngavovqPovqPnmoToirHlv4PjgIIK5LiH5LqL6aG65Yip5piv5Zug5Li65b+D5Lit6Ieq5pyJ5LiA5p2h5piO6Lev44CC",
      "4oCU4oCU5Lit5ZCJ4oCU4oCUCuWNgeW5tOejqOS4gOWJke+8jOS7iuacneekuumcnOWIg+OAggrmgbbov5Dlt7LplIDvvIzouqvkuLTlkKbmnoHms7DmnaXkuYvml7bjgIIK6Ium57uD5aSa5bm05pyq6IO95LiA5pi+6Lqr5omL55qE5omN6IO977yMCueOsOS7iuacieS6huWkp+Wxlei6q+aJi+eahOaegeWlveacuuS8muOAggroi6XmmK/pgYfliLDpmLvnoo3kuYvkuovvvIzkuqbkuI3lv4Xov7fmg5jvvIwK5aSn6IOG5Zyw5ouU5YmR77yM55eb5b+r5Zyw5oiY5paX5LiA55Wq5ZCn44CCCgrku4rlpKnnmoTlubjov5DnianmmK8655Sf6ZW/5aSa5bm055qE44CM5rW354G16Iqd44CN44CCCuW8seWwj+eahOa1t+eBteiKneiZq+e7j+WOhuWkmuW5tOeahOmjjumjjumbqOmbqO+8jOaJjeiDvee7k+aIkOa1t+eBteiKneOAggrkuLrnm67moIfogIzliqrlipvliY3ooYznmoTkurrku6zvvIzmnIDnu4jkuZ/lv4XlsIbmi6XmnInog5zliKnnmoTmnpzlrp7jgII=",
      "4oCU4oCU5Lit5ZCJ4oCU4oCUCuWkqeS4iuacieS6kemjmOi/h+eahOaXpeWtkO+8jOWkqeawlOS7pOS6uuWNgeWIhuiIkueVheOAggrlt6XkvZzpnZ7luLjpobrliKnvvIzov57ljYjnnaHml7bkuZ/kvJrmg7PliLDlpb3ngrnlrZDjgIIK56qB54S25Y+R546w77yM5LiO6ICB5pyL5Y+L6L+Y5pyJ5YW25LuW55qE5YWx5ZCM6K+d6aKYLi4uCuKAlOKAlOavj+S4gOWkqeavj+S4gOWkqemDveimgeenr+aegeW8gOacl+WcsOW6pui/h+KAlOKAlAoK5LuK5aSp55qE5bm46L+Q54mp5pivOuiJsuazveiJs+S4veeahOOAjOWgh+eTnOOAjeOAggrkurrku6zluLjor7Tooajph4zlpoLkuIDmmK/nvo7lvrfvvIwK5L2G5aCH55Oc5piO6Imz55qE5aSW6LKM5LiL6ZqQ6JeP552A55qE5piv6LCm5Y2R6ICM55SY55Sc55qE5YaF5Zyo44CC",
      "4oCU4oCU5ZCJ4oCU4oCUCuS4gOWmguaXouW+gOeahOS4gOWkqeOAgui6q+S9k+WSjOW/g+eBtemDvemAguW6lOS6hueahOaXpeW4uOOAggrlh7rnjrDkuobog73mm7/ku6PlvITkuKLnmoTkuJzopb/nmoTnianlk4HvvIzku6TkurrlvojoiJLlv4PjgIIK5ZKM5bi45bi46YGH6KeB55qE5Lq65YWz57O75Lya5Y+Y5aW977yM5Y+v6IO95Lya5oiQ5Li65pyL5Y+L44CCCuKAlOKAlOaXoOiuuuaYr+WkmuWvu+W4uOeahOaXpeWtkO+8jOmDveiDveaIkOS4uuWunei0teeahOWbnuW/huKAlOKAlAoK5LuK5aSp55qE5bm46L+Q54mp5pivOumXqumXquWPkeS6rueahOOAjOaZtuaguOOAjeOAggrmmbbonbbmmK/lh53ogZrlpKnlnLDpl7TnmoTlhYPntKDvvIzogIzplb/miJDnmoTnu4blsI/nlJ/nianjgIIK6ICM5YWD57Sg5piv6L+Z5Liq5LiW55WM6K645Lul5aSp5Zyw5b2T5Lit55qE5Lq65Lus55qE56Wd56aP44CC",
      "4oCU4oCU5ZCJ4oCU4oCUCuaYjuaYjuayoeacieS7gOS5iOeJueWIq+eahOS6i+aDhe+8jOWNtOaEn+WIsOW/g+aDhei9u+W/q+eahOaXpeWtkOOAggrlnKjmsqHms6jmhI/ov4fnmoTop5LokL3lj6/ku6Xmib7liLDmnKzku6XkuLrkuKLlpLHlt7LkuYXnmoTkuJzopb/jgIIK6aOf54mp5q+U5bmz5pe25pu05Yqg6bKc576O77yM6Lev5LiK55qE6aOO5pmv5Lmf5Luk5Lq655y85YmN5LiA6auY44CCCuKAlOKAlOi/meS4quS4lueVjOS4iuWFhea7oeS6huaWsOWlh+eahOe+juWlveS6i+eJqeKAlOKAlAoK5LuK5aSp55qE5bm46L+Q54mp5pivOuaVo+WPkeaaluaEj+eahOOAjOm4n+ibi+OAjeOAggrpuJ/om4vlrZXogrLnnYDml6DpmZDnmoTlj6/og73mgKfvvIzmmK/mnKrmnaXkuYvnp43jgIIK5Y+N6L+H5p2l77yM6L+Z5Liq5LiW55WM5a+56bif6JuL5Lit55qE55Sf5ZG96ICM6KiA77yMCuS5n+WFhea7oeS6huS7pOWFtuWFtOWli+eahOacquefpeS6i+eJqeWQp+OAggropoHmuKnmn5Tlr7nlvoXpuJ/om4vllpTjgII=",
      "4oCU4oCU5ZCJ4oCU4oCUCuaer+acqOmAouaYpe+8jOato+W9k+S4h+eJqeWkjeiLj+S5i+aXtuOAggrpmbflhaXlm7DlooPml7bvvIzog73lvpfliLDop6PlhrPlip7ms5XjgIIK5Li+5qOL5LiN5a6a5pe277yM5Lya5pyJ6LS15Lq65p2l55u45Yqp44CCCuWPr+S7peaVtOmhv+S4gOeVquW/g+aDhe+8jOa4heeQhuS4gOeVquWutuijhe+8jAror7TkuI3lrprog73lj5HnjrDmhI/lpJbkuYvotKLjgIIKCuS7iuWkqeeahOW5uOi/kOeJqeaYrzroioLoioLpq5jljYfnmoTjgIznq7nnrIvjgI3jgIIK56u556yL5oul5pyJ552A5peg6ZmQ55qE5r2c5Yqb77yMCuayoeacieS6uuefpemBk+S4gOmil+erueesi++8jOWIsOW6leiDvemVv+aIkOWkmumrmOeahOerueWtkOOAggrnnIvnnYDnq7nnrIvvvIzkvJrorqnkurrkuI3nlLHoh6rkuLvmnJ/lvoXotbfmnKrmnaXlkKfjgII=",
      "4oCU4oCU5pyr5ZCJ4oCU4oCUCuawlOWOi+eojeW+ruacieeCueS9ju+8jOaYr+S8muS7pOS6uuaDs+WIsOmBpei/nOeahOi/h+WOu+eahOaXpeWtkOOAggrml6nlt7Lov4flvoDnmoTlubTovbvlsoHmnIjvvIzkuI7lho3msqHogZTns7vov4fnmoTmlYXlj4vnmoTlm57lv4bvvIwK5Lya6K6p5Lq65oSf5Yiw5Lid5bmz5reh55qE5oCA5b+177yM5Y+I56iN5b6u5pyJ5LiA54K554K55oSf5Lyk44CCCuKAlOKAlOWBtuWwlOaAgOW/tei/h+WOu+S5n+W+iOWlveOAguaUvuadvuW/g+aDhemdouWvueacquadpeWQp+KAlOKAlAoK5LuK5aSp55qE5bm46L+Q54mp5pivOua4heaWsOaAoeS6uueahOOAjOiWhOiNt+OAjeOAggrlj6ropoHmnInojYnmnKjnlJ/plb/nmoTnqbrpl7TvvIzlsLHkuIDlrprmnInoloTojbfjgIIK6L+Z5LmI55yL5p2l77yM6JaE6I235piv5LiW55WM5LiK5pyA5by66Z+n55qE55Sf54G144CCCuaNruivtOi/nuiSmeW+t+eahOmbquWxseS4iuS5n+mVv+edgOiWhOiNt+WRouOAgg==",
      "4oCU4oCU5pyr5ZCJ4oCU4oCUCuepuuS4reeahOS6keWxguWBj+S9ju+8jOW5tuS4lOS7jeacieWghuenr+S5i+WKv++8jArkuI3nn6XkvZXml7bpm7fpm6jkvJrpqqTnhLbku47lpLTpobblgL7nm4bogIzkuIvjgIIK5L2G5piv562J6Zu36Zuo6L+H5ZCO77yM6L+Y5Lya5pyJ5b2p6Jm55Zyo562J552A44CCCuWunOW+quS6juaXp++8jOWuiOS6jumdme+8jOiLpeWmhOS4uuWImemavuaIkOS5i+OAggoK5LuK5aSp55qE5bm46L+Q54mp5pivOuagkeS4iuaOieiQveeahOOAjOadvuaenOOAjeOAggrlubbkuI3mmK/miYDmnInnmoTmnb7mnpzpg73og73plb/miJDpq5jlpKfnmoTmnb7moJHvvIwK5oiQ6ZW/6ZyA6KaB6YCC5a6c55qE546v5aKD77yM5pu06ZyA6KaB5LiA54K56L+Q5rCU44CCCuaJgOS7peS4jeeUqOe7meiHquW3sei/h+WkmuWOi+WKm++8jOiAkOW/g+etieW+heW9qeiZueWQp+OAgg==",
      "4oCU4oCU5pyr5ZCJ4oCU4oCUCuS6kemBruaciOWNiui+ue+8jOmbvui1t+abtOi/t+emu+OAggrmiqzlpLTljbPmmK/mta7kupHpga7mnIjvvIzkvY7lpLTliJnmmK/mtZPpm77mvKvmvKvjgIIK6Jm954S25LiA5pe25YmN6Lev6L+35oOY77yM5L2G5Lmf5Lya5pyJ5LiA5YiH5piO5LqG55qE5pe25Yi744CCCueOsOS4i+S4jeWmgui2geatpOacuuS8muejqOeCvOiHquaIke+8jOetieW+heaLqOS6keingeeajuaciOOAggoK5LuK5aSp55qE5bm46L+Q54mp5pivOuaal+S4reWPkeS6rueahOOAjOWPkeWFiemrk+OAjeOAggrlj5HlhYnpq5PliqrlipvlnLDlj5Hlh7rlvq7lvLHnmoTlhYnoipLjgIIK6Jm954S25q+U5LiN6L+H5YW25LuW5YWJ5rqQ77yM5L2G55yL5riF5YmN6Lev5Lmf5aSf55So5LqG44CC",
      "4oCU4oCU5pyr5ZCJ4oCU4oCUCuW5s+eos+WuieivpueahOS4gOWkqeOAguayoeacieS7gOS5iOS7pOS6uumavui/h+eahOS6i+aDheS8muWPkeeUn+OAggrpgILlkIjlkozkuYXmnKrogZTns7vnmoTmnIvlj4vogYrogYrov4fljrvnmoTkuovmg4XvvIzkuIDlkIzmrKLnrJHjgIIK5ZCD5Lic6KW/55qE5pe25YCZ5Lya5bCd5Yiw5b6I5LmF5Lul5YmN5L2T6aqM6L+H55qE6L+H5Y6755qE5ZGz6YGT44CCCuKAlOKAlOimgeePjeaDnOi6q+i+ueeahOS6uuS4juS6i+KAlOKAlAoK5LuK5aSp55qE5bm46L+Q54mp5pivOumFpemFpem6u+m6u+eahOOAjOeUteawlOawtOaZtuOAjeOAggrnlLXmsJTmsLTmmbbolbTlkKvnnYDml6DpmZDnmoTog73ph4/jgIIK5aaC5p6c6IO95aSf5aW95aW95a+85byV6L+Z6IKh6IO96YeP77yM6K+05LiN5a6a5bCx6IO95oiQ5bCx5LuA5LmI5LqL5Lia44CC",
      "4oCU4oCU5Ye24oCU4oCUCumakOe6puaEn+inieS8muS4i+mbqOeahOS4gOWkqeOAguWPr+iDveS8mumBh+WIsOS4jemhuuW/g+eahOS6i+aDheOAggrlupTor6XnmoTopJLlpZbov5/ov5/msqHmnInliLDmnaXvvIzmnI3liqHnlJ/kuZ/lj6/og73kvJrkuIrplJnoj5zjgIIK5piO5piO5rKh5LuA5LmI5aSn5LiN5LqG55qE5LqL77yM5Y205oC75oSf6KeJ5pyJ5Lqb5b+D54Om55qE5pel5a2Q44CCCuKAlOKAlOmavuWFjeaciei/meagt+eahOaXpeWtkOKAlOKAlAoK5LuK5aSp55qE5bm46L+Q54mp5pivOumaj+azouaRh+abs+eahOOAjOa1t+iNieOAjeOAggrmtbfojYnmmK/nm7jlvZPmuKnmn5TogIzlnZrlvLrnmoTmpI3nianvvIwK5Y2z5L2/5Zyo6Ium5rap55qE5rW35rC05Lit77yM5Lmf5LiN5oS/5pS55Y+Y6Ieq5bex44CCCuWNs+S9v+WcqOmAhuWig+S4re+8jOS5n+S4jeimgeaUvuW8g+a4qeaflOeahOW/g+eBteOAgg==",
      "4oCU4oCU5Ye24oCU4oCUCuePjeaDnOeahOS4nOilv+WPr+iDveS8mumBl+Wkse+8jOmcgOimgeWwj+W/g+OAggrlpoLmnpzouqvkvZPmnInkuI3pgILvvIzkuIDlrpropoHms6jmhI/kvJHmga/jgIIK5Zyo5YGa5Ye65Yaz5a6a5LmL5YmN77yM5LiA5a6a6KaB5YaN5LiJ5oCd6ICD44CCCgrku4rlpKnnmoTlubjov5DnianmmK865Yaw5YeJ5Yaw5YeJ55qE44CM5Yaw6Zu+6Iqx44CN44CCCuWGsOmbvuiKseaVo+WPkeedgOOAjOeUn+S6uuWLv+i/m+OAjeeahOWvkuawlOOAggrkvYbmnInml7blhrDlhrfnmoTmsJTotKjvvIzkuZ/og73orqnkurrnmoTlv4Pmg4XkuI7lpLTohJHlhrfpnZnkuIvmnaXjgIIK5o2u5q2k6YeH5Y+W5q2j56Gu55qE5Yik5pat77yM5piO5pm65Zyw6KGM5Yqo44CC",
      "4oCU4oCU5aSn5Ye24oCU4oCUCuWGheW/g+epuuiQveiQveeahOS4gOWkqeOAguWPr+iDveS8mumZt+WFpea3sea3seeahOaXoOWKm+aEn+S5i+S4reOAggrlvojlpJrkuovmg4Xpg73ml6Dms5XnkIbmuIXlpLTnu6rvvIzov4fkuo7pkrvniZvop5LlsJbliJnmmJPnlJ/nl4XjgIIK6Jm954S25LiA5YiH55qG6Zm35LqO5L2O5r2u6LC35bqV5Lit77yM5L2G5Lmf5LiN5b+F5Zug5q2k6ICM5rCU6aaB44CCCuiLpeiDveaSkei/h+S4gOaXtuWbsOWig++8jOS7luaXpeW/heWPpuacieS4gOeVquS9nOS4uuOAggoK5LuK5aSp55qE5bm46L+Q54mp5pivOuW8r+W8r+absuabsueahOOAjOicpeictOWwvuW3tOOAjeOAggronKXonLTpgYfliLDmvZzlnKjnmoTljbHpmanml7bvvIzlpKflpJrmlbDkvJrmlq3lsL7msYLnlJ/jgIIK6Iul5piv6YGH5Yiw5peg5rOV5pW055CG55qE5oOF57uq77yM6YKj5LmI6K+l5pat5YiZ5pat5ZCn44CC"
    );

    $ret = "\n今天已经摇过了，明天再来看吧，今天摇到的签是\n";
    if (!$this->redisExists("plugins-slip-" . $msgSender)) {
      $t = TIME_T;
      $endTime = strtotime(date("Y-m-d 04:00:00", strtotime("+1 day")));
      $expireTime = $endTime - $t;

      $this->redisSet("plugins-slip-" . $msgSender, $slip[array_rand($slip)], $expireTime);

      $ret = null;
    };
    $ret .= "\n" . base64_decode($this->redisGet("plugins-slip-" . $msgSender));

    return $ret;
  }
}
