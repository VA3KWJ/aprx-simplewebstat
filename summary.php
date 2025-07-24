<?php

	/******************************************************************************************
		This file is a part of SIMPLE WEB STATICSTICS GENERATOR FROM APRX LOG FILE

        Original Author: Peter SQ8VPS, sq8vps[--at--]gmail.com & Alfredo IZ7BOJ

		You can modify this program, but please give a credit to original author.
		Program is free for non-commercial use only.
		
        (C) Peter SQ8VPS & Alfredo IZ7BOJ 2017-2018

        Modified by Ryan KF6ODE to remove language localazation, add some features, and
        overall improve the code.

		Version 1.3
  
  	Modified by Stu VA3KWJ to apply PHP8.3 fixes
   		Version 1.4

	*******************************************************************************************/

    include 'config.php';
    include 'common.php';
    include 'functions.php';

    session_start();                                                            //start session
    
    if ( !isset( $_SESSION['if'] ) )                                            // if interface not defined
    {
        header('Refresh: 0; url=chgif.php?chgif=1');
        die();
    }
    
    $call               = $_SESSION['call'];
    $callraw            = $_SESSION['if'];
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="Description" content="APRX statistics" />
        <meta name="Keywords" content="" />
        
        <link rel="stylesheet" type="text/css" href="style.css">

        <title>APRX Dashboard</title>
    </head>
    <body>
        <div class="header-container">
            <?php
                if ( file_exists( $logourl ) ) {
            ?>

            <div class="image-left">
                <img src="aprslogo.png">
            </div>

        <?php
            }
        ?>

            <div class="title-left">
                <ul>
                    <li>APRX Dasboard</li>
                    <li>for interface <font color="red"><b><?php echo $call ?></b></font></li>
                </ul>
            </div>
            <div class="header-nav">
                <div class="nav-link">
                    <a href="summary.php">Dashboard</a>
                </div>
                <div class="nav-link">
                    <a href="chgif.php?chgif=1">Change Interface</a>
                </div>
                <div class="nav-link">
                    <a href="live.php">Realtime AX.25 Traffic</a>
                </div>
            </div>
        </div>
        <div class="separator">
            <hr>
        </div>
        <div class="stats-container">
            <div class="table-container-left">
                <ul class="responsive-table">
                    <li class="table-header">
                        <div class="table-header">APRS STATS</div>
                    </li>
                    <li class="table-row">

                    <?php
                        $lines              = 0;
                        $rx                 = 0;
                        $tx                 = 0;
                        $is                 = 0;
                        $other              = 0;
                        $receivedstations   = array();
                        $time               = 0;
                        //$framespermin       = 0;
                        $time1              = 0;
                        $time2              = 0;
                        $isservers          = array();
                        $interfaces         = array();
                        $time               = 0;                                                    // start of the time from which to read data from log in Unix timestamp type
                        $staticstations     = array();
                        $movingstations     = array();
                        $otherstations      = array();
                        $directstations     = array();                                              // stations received directly
                        $viastations        = array();                                              // stations received via digi
                        
                        switch ($_GET) {
                            case empty( $_GET['time'] ):                                            // if time range not specified
                                $time = time() - 3600;                                              // take frames from last 1 hour
                                break;
                        
                            case ($_GET['time'] ?? '') == "e":                                              // if whole log. Parse Fix
                                $time = 0;
                                break;
                            
                            default:                                                                // otherwise the time range is choosen
                                $time = time() - ( $_GET['time'] * 3600 );                          // convert hours to seconds
                        }
                        
                        $logfile            = file($logpath);                                       // read log file
                        $linesinlog         = count($logfile);
                        
                        while ( $lines < $linesinlog )                                              // read line by line and increment counter based on the frame type
                        {                                            
                            $line           = $logfile[$lines];
                            
                            stationparse($line);
                        
                            switch (true) {
                                case strpos( $line, $callraw." R" ):                                // from radio
                                    $rx++;
                                    break;
                                
                                case strpos( $line, $callraw." d" ):                                // from radio
                                    $rx++;
                                    break;
                            
                                case strpos( $line, "APRSIS    R" ):                                // from aprs-is
                                    $is++;
                                    break;
                                
                                case strpos( $line, $callraw." T" ):                                // tx by radio
                                    $tx++;
                                    break;
                        
                                default:                                                            // something else
                                    $other++;
                            }
                        
                            $lines++;
                        }
                        
                        // custom sorting function
                        function cmp($a, $b) {
                            if ( $a[1] == $b[1] ) {
                                return 0;
                            }
                            return ($a[1] > $b[1]) ? -1 : 1;
                        }
                                                                    
                        uasort($receivedstations, 'cmp');
                        
                        // System parameters reading
                        $sysver             = NULL;
                        $kernelver          = NULL;
                        $aprxver            = NULL;
                        $cputemp            = NULL;
                        $cpufreq            = NULL;
                        $uptime             = NULL;
                        
                        $sysver             = shell_exec ("cat /etc/os-release | grep PRETTY_NAME |cut -d '=' -f 2");
                        $kernelver          = shell_exec ("uname -r");
                        $aprxver            = shell_exec ("sudo aprx --v | grep version: | cut -d ':' -f 2");
                        
                        // following command works only if aprx is installed using apt-get
                        //$aprxver = shell_exec ("apt-cache policy aprx | grep Installed | cut -d ':' -f 2");
                        
                        if ( file_exists( "/sys/class/thermal/thermal_zone0/temp" ) ) {
                            exec(
                                "cat /sys/class/thermal/thermal_zone0/temp",
                                $cputemp
                            );
                            
                            $cputemp        = $cputemp[0] / 1000;
                        }
                        
                        if  ( file_exists( "/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq" ) ) {
                            exec(
                                "cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq",
                                $cpufreq
                            );
                            
                            $cpufreq        = $cpufreq[0] / 1000;
                        }
                        
                        $uptime = shell_exec('uptime -p');
                        
                        $cyclesize          = NULL;
                        $myloc              = NULL;
                        $server             = NULL;
                        $txactive           = NULL;
                        $numradioint        = NULL;
                        $digiactive         = NULL;
                        
                        //parse parameters in aprx.conf using shell commands and grep. Don't consider commented rows and blanks
                        $cyclesize          = shell_exec ( "cat $confpath | grep '^[[:blank:]]*[^[:blank:]#]' | grep cycle-size | awk  '{print $2}'" );
                        $myloc              = shell_exec ( "cat $confpath | grep '^[[:blank:]]*[^[:blank:]#]' | grep 'myloc' | grep -v 'beacon' | cut -d ' ' -f2-" );
                        $server             = shell_exec ( "cat $confpath | grep '^[[:blank:]]*[^[:blank:]#]' | grep server |  awk  '{print $2}'" );
                        $txactive           = shell_exec ( "cat $confpath | grep '^[[:blank:]]*[^[:blank:]#]' | grep tx-ok |  awk  '{print $2}'" );
                        $numradioint        = shell_exec ( "cat $confpath | grep '^[[:blank:]]*[^[:blank:]#]' | grep serial-device -c" );
                        $digiactive         = shell_exec ( "cat $confpath | grep '^[[:blank:]]*[^[:blank:]#]' | grep '<digipeater>' -c" );
                        

                        echo '
                                    <div class="col col-1">Frames in Log</div>
                                    <div class="col col-2">'.($rx + $tx + $is + $other).'</div>
                                </li>
                                <li class="table-row">
                                    <div class="col col-1">Frames RX via RF</div>
                                    <div class="col col-2">'.$rx.'</div>
                                </li>
                                <li class="table-row">
                                    <div class="col col-1">Frames TX via RF</div>
                                    <div class="col col-2">'.$tx.'</div>
                                </li>
                                <li class="table-row">
                                    <div class="col col-1">Frames From APRS-IS</div>
                                    <div class="col col-2">'.$is.'</div>
                                </li>';
                        
                        rxload();
                        
                        echo '
                                <li class="table-row">
                                    <div class="col col-1">RX Load (last 20 frames)</div>
                                    <div class="col col-2">'.number_format($rxframespermin, 2, '.', ',').' frames/min</div>
                                </li>';
                        
                        txload();
                        
                        echo '
                        <li class="table-row">
                            <div class="col col-1">TX Load (last 20 frames)</div>
                            <div class="col col-2">'.number_format($txframespermin, 2, '.', ',').' frames/min</div>';
                    ?>

                    </li>
                </ul>
            </div>

            <div class="table-container-left">
                <ul class="responsive-table">
                    <li class="table-header">
                        <div class="table-header">SYSTEM STATUS</div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">System Version</div>
                        <div class="col col-2"><?php echo $sysver ?></div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">Kernel Version</div>
                        <div class="col col-2"><?php echo $kernelver ?></div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">APRX Version</div>
                        <div class="col col-2"><?php echo $aprxver ?></div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">System uptime</div>
                        <div class="col col-2"><?php echo $uptime ?></div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">CPU temperature</div>
                        <div class="col col-2"><?php echo $cputemp ?> °C </div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">CPU frequency</div>
                        <div class="col col-2"><?php echo $cpufreq ?> MHz </div>
                    </li>
                </ul>
            </div>

            <div class="table-container-left">
                <ul class="responsive-table">
                    <li class="table-header">
                        <div class="table-header">APRX CONFIG PARAMETERS</div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">Beacon Interval</div>
                        <div class="col col-2"><?php echo $cyclesize ?></div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">APRS-IS server</div>
                        <div class="col col-2"><?php echo $server ?></div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">Location</div>
                        <div class="col col-2"><?php echo $myloc ?></div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">Radio Ports</div>
                        <div class="col col-2"><?php echo $numradioint ?></div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">RF Transmit</div>
                        <div class="col col-2">
                            <?php
                                // echo $txactive;
                                if  ( $txactive == true )   echo '<font color="green"><b>ACTIVE</b></font>';
                                else                        echo '<font color="red"><b>INACTIVE</b></font>';   
                            ?>
                        </div>
                    </li>
                    <li class="table-row">
                        <div class="col col-1">Digipeater</div>
                        <div class="col col-2">
                            <?php
                                if ( $digiactive == 1 ) echo '<font color="green"><b>ACTIVE</b></font>';
                                else                    echo '<font color="red"><b>INACTIVE</b></font>';        
                            ?>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="separator">
            <hr>
        </div>

        <div class="form">
            <form action="summary.php" method="GET">
                <div class="center">
                    Filter Stations by Time
                </div>
                <div class="custom-select">
                    <select name="time">
                        <option value="1"   <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 1   ) )  echo 'selected="selected"'?> >Last Hour</option>
                        <option value="2"   <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 2   ) )  echo 'selected="selected"'?> >Last 2 Hours</option>
                        <option value="4"   <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 4   ) )  echo 'selected="selected"'?> >Last 4 Hours</option>
                        <option value="8"   <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 8   ) )  echo 'selected="selected"'?> >Last 8 Hours</option>
                        <option value="12"  <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 12  ) )  echo 'selected="selected"'?> >Last 12 Hours</option>
                        <option value="24"  <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 24  ) )  echo 'selected="selected"'?> >Last Day</option>
                        <option value="48"  <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 48  ) )  echo 'selected="selected"'?> >Last 2 days</option>
                        <option value="168" <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 168 ) )  echo 'selected="selected"'?> >Last Week</option>
                        <option value="720" <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 720 ) )  echo 'selected="selected"'?> >Last Month</option>
                        <option value="e"   <?php if ( isset( $_GET['time'] ) && ( $_GET['time'] == 'e' ) )  echo 'selected="selected"'?> >ALL</option>
                    </select>
                </div>
                <script src="select.js"></script>
                <div class="submit-right">
                    <input type="submit" class="submit" value="Refresh">
                </div>
            </form>
        </div>
        
        <script src="sorttable.js"></script>

        <div class="table-center">
            <table class="sortable" id="table">
                <caption class="table-caption"><?php echo count($receivedstations).' Stations received on radio (sorted by Last Time Heard)' ?></caption>
                <thead>
                    <tr>
                        <th>Callsign</th>
                        <th>Points</th>
                        <!-- <th>Map show</th> -->
                        <th>Raw packet</th>
                        <th>Details</th>
                        <th>STATIC/Moving</th>
                        <th>Via</th>
                        <th>Last time Heard</th>
                    </tr>
                </thead>
                <tbody>

		<?php
                        foreach ($receivedstations as $c => $nm) {
                ?>

                    
                    <tr>
                        <td class="callsign"><?php echo '<a target="_blank" href="https://aprs.fi/?call='.$c.'">'.$c.'</a>' ?></td>
                        <td class="center"><?php echo $nm[0] ?></td>
                        <!-- <td><?php echo '<a target="_blank" href="https://aprs.fi/?call='.$c.'">Map</a>'?></td> -->
                        <td><?php echo '<a target="_blank" href="frames.php?getcall='.$c.'">RAW Packets</a>'?></td>
                        <td><?php echo '<a target="_blank" href="details.php?getcall='.$c.'">Details</a>' ?></td>
                        <td>
                            <?php
                                if      ( in_array ( $c, $staticstations ) ) echo '<font color="purple">STATIC</font>';
                                elseif  ( in_array ( $c, $movingstations ) ) echo '<font color="orange">MOVING</font>';
                                else                                         echo "OTHER";
                            ?>
                        </td>
                        <td>
                            <?php
                                if      ( ( in_array ( $c, $directstations ) ) && ( in_array( $c, $viastations ) ) ) echo '<font color="BLUE">DIGI+DIRECT</font>';
                                elseif  (   in_array ( $c, $directstations ) )                                       echo '<font color="RED">DIRECT</font>';
                                else if (   in_array ( $c, $viastations ) )                                          echo '<font color="GREEN">DIGI</font>';
                            ?>
                        </td>
                        <td>
                            <?php
                                echo ( date( 'm/d/Y H:i:s', $nm[1] ) )
                            ?>
                        </td>
                    </tr>
                    
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="separator">
            <hr>
        </div>
        <div class="footer">
		<p><a href="https://github.com/VA3KWJ/aprx-simplewebstat" target="_blank">APRX Simple Webstat</a> updated by <a href="https://www.qrz.com/db/VA3KWJ" target="_blank">Stuart</a>. Visit me: <a href="https://va3kwj.ca" target="_blank">VA3KWJ</a> | Original <a href="https://github.com/sq8vps/aprx-simplewebstat" target="_blank" >APRX Simple Webstat</a> by Peter SQ8VPS and Alfredo IZ7BOJ</p>
        </div>
    </body>
</html>
