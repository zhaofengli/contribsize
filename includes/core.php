<?php
/*
	Copyright (c) 2014, Zhaofeng Li
	All rights reserved.
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	* Redistributions of source code must retain the above copyright notice, this
	list of conditions and the following disclaimer.
	* Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
	SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
	CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
	OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
	OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

include __DIR__ . "/config.php";

function connectDb() {
	global $dbhost, $dbname, $dbuser, $dbpass;
	$db = new PDO( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass );
	return $db;
}

function getSize( $username ) {
	$db = connectDb();
	$sql = "select rev_len,rev_parent_id from revision_userindex where rev_user_text=:username";
	$stmt = $db->prepare( $sql );
	$stmt->bindValue( ":username", $username );
	if ( $stmt->execute() ) {
		$size = 0;
		$newlen = array();
		$parents = array();
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
		        if ( $row['rev_parent_id'] == 0 ) { // new page
		                $size += $row['rev_len'];
		        } else {
		                $parents[] = $row['rev_parent_id'];
		                $newlen[$row['rev_parent_id']] = $row['rev_len'];
		        }
		}
		if ( count( $parents ) ) {
			if ( $parentq = $db->query( "select rev_id,rev_len from revision where rev_id in (" . implode( ",", $parents ) . ")" ) ) {
				while ( $row = $parentq->fetch( PDO::FETCH_ASSOC ) ) {
					$size += abs( $newlen[$row['rev_id']] - $row['rev_len'] );
				}
			}
		}
	}
	$db = null;
	return $size;
}
