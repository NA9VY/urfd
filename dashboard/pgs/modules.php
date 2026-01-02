<table class="table table-bordered table-hover">
 <tr>
   <th width="80" rowspan="2">Module</th>
   <th width="130" rowspan="2">Name</th>
   <th width="65" rowspan="2">Users</th>
   <th colspan="2">DPlus</th>
   <th colspan="2">DExtra</th>
   <th colspan="2">DCS</th>
   <th width="65" rowspan="2">DMR</th>
   <th width="65" rowspan="2">YSF<br />DG-ID</th>
 </tr>
 <tr>
   <th width="100">URCALL</th>
   <th width="100">DTMF</th>
   <th width="100">URCALL</th>
   <th width="100">DTMF</th>
   <th width="100">URCALL</th>
   <th width="100">DTMF</th>
 </tr>
<?php

$ReflectorNumber = substr($Reflector->GetReflectorName(), 3, 3);
$NumberOfModules = isset($PageOptions['NumberOfModules']) ? min(max($PageOptions['NumberOfModules'],0),9) : 9;

$odd = "";

for ($i = 1; $i <= $NumberOfModules; $i++) {

   $module = chr(ord('A')+($i-1));

   if ($odd == "#FFFFFF") { $odd = "#F1FAFA"; } else { $odd = "#FFFFFF"; }

   echo '
 <tr height="30" bgcolor="'.$odd.'" onMouseOver="this.bgColor=\'#FFFFCA\';" onMouseOut="this.bgColor=\''.$odd.'\';">
   <td align="center">'. $module .'</td>
   <td align="center">'. (empty($PageOptions['ModuleNames'][$module]) ? '-' : $PageOptions['ModuleNames'][$module]) .'</td>
   <td align="center">'. count($Reflector->GetNodesInModulesByID($module)) .'</td>
   <td align="center">'. 'REF' . $ReflectorNumber . $module . 'L' .'</td>
   <td align="center">'. (is_numeric($ReflectorNumber) ? '*' . sprintf('%01d',$ReflectorNumber) . (($i<=4)?$module:sprintf('%02d',$i)) : '-') .'</td>
   <td align="center">'. 'XRF' . $ReflectorNumber . $module . 'L' .'</td>
   <td align="center">'. (is_numeric($ReflectorNumber) ? 'B' . sprintf('%01d',$ReflectorNumber) . (($i<=4)?$module:sprintf('%02d',$i)) : '-') .'</td>
   <td align="center">'. 'DCS' . $ReflectorNumber . $module . 'L' .'</td>
   <td align="center">'. (is_numeric($ReflectorNumber) ? 'D' . sprintf('%01d',$ReflectorNumber) . (($i<=4)?$module:sprintf('%02d',$i)) : '-') .'</td>
   <td align="center">'. (4000+$i) .'</td>
   <td align="center">'. (9+$i) .'</td>
 </tr>';
}

?>

</table>

<p><b>What are Modules?</b></p>
<br>
<p>Modules could also be called chat rooms. They are different areas within a reflector to have conversations that are separate from other modules.
While not required on most reflectors, you might also say the main module on a reflector, often Module A, is the calling module and once you've
established contact with the person you are calling you can both move to a different module to continue the conversation.</p>
<br>
<p>Reflector operators have to option of assigning names or topic to the modules they have on their reflector. For example, some operators may dedicate
modules to AMSAT or POTA conversations while others call the modules Chat Room B and Chat Room C. How this is done is up to the person that operates 
the reflector.</p>
<br>
<p>Depending on the configuration of the reflector, one or more modules can also be transcoded to support multiple modes (D-Star, YSF, DMR, etc.) of
operation at the same time. Modules that are not transcoded can only support one mode at a time.</p>
<br>
<p>How a user moves between modules depends on the mode they are using. YSF users transmit with the module's assigned DGID to
access the module they would like to use. DMR users make a private
call to the module's assigned talkgroup and then move back to group call talk group to start a conversation.
D-Star users use the module's assigned URCALL to move to different modules.<p>
