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
	*******************************************************************************************/
	
	include 'config.php';
	include 'common.php';

	path_check();

	if (                                                                        // check to see if no interface was specified
		( !isset( $_SESSION['if'] ) )                                           // no interface selected
		or
		( isset( $_SESSION['if'] ) and  ($_SESSION['if'] == "" ) )              // interface is selected, but it is a null string
	)
	{
		if ( ( $static_if == 1 ) && ( $static_call != "" ) )                    // check to see if a static call is set in config.php
		{                                                                       // if it is, start a session and redirect to the summary page
			session_start();

			$callsign			= strtoupper( $static_call ); 					//uppercase the static callsign
			$_SESSION['call']	= $callsign;
			$callsignraw		= $callsign;
			
			while ( strlen( $callsignraw ) < 9 )
			{
				$callsignraw	.= " ";											// add spaces to raw callsign
			}

			$_SESSION['if']	= $callsignraw;

			header('Refresh: 0; url=summary.php');
		}
		else
		{
			header('Refresh: 0; url=chgif.php?chgif=1');                        // if no static call is set, redirect to the interface selection page
		}

		die();
	}
	else																		// we have an interface, so redirect to the summary page
	{
		header('Refresh: 0; url=summary.php');
		die();
	}
?>
