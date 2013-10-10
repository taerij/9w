<?php
class Hex_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function user_names(){
		$query="
			SELECT  firstname +' '+ lastname as name, userid as id
			FROM [legrandv6].[dbo].[lfapUsers]
			WHERE isactive = 1
			";  
			$rs = $this->db->query($query);
			$grab  = $rs->result_array();
			
			// pretty sure there's a better way to do this...
			$array = array();
			 
			foreach($grab as $row){
				$array[$row["id"]] = $row["name"];
			}
			 
			 //var_dump($array);
			
			return $array;
			  
		}
	
	function get_number_opps(){
		//count how many active opportunities each user has
		$query="
			SELECT u.userid, count(*) as opps
			FROM [legrandv6].[dbo].[lfapUsers] as u
			JOIN [dbo].[opportunities] AS o ON o.mgrid = u.userid
			WHERE o.iactive = 1
			AND u.isactive = 1
			AND u.userid != 'CRM01'
			GROUP BY u.userid
			
			"; //u.firstname, u.lastname,
			return $this->db->query($query);			
	}
	function get_pipeline(){
		// sum how much value all active opportunities is worth, per user
		//$i = 1;
		//foreach ($userarray as $key	=> $value){
			$query="
				SELECT mgrid, sum(yestvalue) as total, sum(oppamount3) as margin, count(*)
				FROM [legrandv6].[dbo].[opportunities] 		
				WHERE iactive = 1
				GROUP BY mgrid
				";
			$rs = $this->db->query($query); //WHERE o.mgrid = '".$key."'
			$grab  = $rs->result_array();	
			//var_dump($grab);
			// pretty sure there's a better way to do this...
			$array = array();
			 
			foreach($grab as $row){
				$array[$row["mgrid"]] = array(
					'total'		=>	$row["total"],
					'margin'	=>	$row["margin"],
				);	
			}
 
			return $array;
	}
	function stats_new_business(){
		//count total new business for this month per user
		$month = date('m', time());
		$year = date('y', time());
		
		$last = date ('Y-m-d', mktime(0,0,0,$month,0,$year));
		
		$query="
			SELECT u.userid, count(*) as opps
			FROM [legrandv6].[dbo].[lfapUsers] as u
			JOIN [dbo].[opportunities] AS o ON o.mgrid = u.userid
			WHERE o.dcreated > '".$last."'
			AND o.dcreated != '".$last."'
			AND isactive = 1
			
			GROUP BY u.userid
			";//AND o.dcreated != '".$yesterday."'
			//echo $query;
			$rs = $this->db->query($query);
			$grab  = $rs->result_array();
			return $grab;		
	}	
	function stats_new_business_values(){
		//count total value of business for this month per user
		$month = date('m', time());
		$year = date('y', time());
		
		$last = date ('Y-m-d', mktime(0,0,0,$month,0,$year));
		
		$query="
			SELECT mgrid, sum(yestvalue) as total, sum(oppamount3) as margin
			FROM [legrandv6].[dbo].[opportunities] 
 			WHERE dcreated > '".$last."'
			AND dcreated != '".$last."'
			
			GROUP BY mgrid
			";
			
			$rs = $this->db->query($query);
			$grab  = $rs->result_array();
 			$array = array();
			 
			foreach($grab as $row){
				$array[$row["mgrid"]] = array(
					'total'		=>	$row["total"],
					'margin'	=>	$row["margin"],
				);	
			}
 
			return $array;
	}	
	
	function stats_closed_business(){
		//count total closed business for this month per user
		$month = date('m', time());
		$year = date('y', time());
		
		$last = date ('Y-m-d', mktime(0,0,0,$month,0,$year));
		
		$query="
			SELECT u.userid, count(*) as opps
			FROM [legrandv6].[dbo].[lfapUsers] as u
			JOIN [dbo].[opportunities] AS o ON o.mgrid = u.userid
			WHERE o.dactclose > '".$last."'
			AND o.dactclose != '".$last."'
			AND isactive = 1
			AND ioutcome = 1
			GROUP BY u.userid
			";//AND o.dcreated != '".$yesterday."'
			//echo $query;
			// 
			
			$rs = $this->db->query($query);
			$grab  = $rs->result_array();
			return $grab;		
	}	
	function stats_closed_business_values(){
		//count total value of business for this month per user
		$month = date('m', time());
		$year = date('y', time());
		
		$last = date ('Y-m-d', mktime(0,0,0,$month,0,$year));
		
		$query="
			SELECT mgrid, sum(yestvalue) as total, sum(oppamount3) as margin
			FROM [legrandv6].[dbo].[opportunities] 
 			WHERE dactclose > '".$last."'
			AND dactclose != '".$last."'
			
			GROUP BY mgrid
			";
			
			$rs = $this->db->query($query);
			$grab  = $rs->result_array();
 			$array = array();
			 
			foreach($grab as $row){
				$array[$row["mgrid"]] = array(
					'total'		=>	$row["total"],
					'margin'	=>	$row["margin"],
				);	
			}
			return $array;
	}
	
	function cold_calls_stats(){

		$yesterday = date('d-M-Y',strtotime('-1 second'));
		//$today =  date('d-M-Y');
		$typelu = '_20111028113624SRKERHE';
		
		$query ="
			SELECT count(*) as total, userid 
			FROM [legrandv6].[dbo].[notes]
 			WHERE typelu = '".$typelu."'
			
			AND dcreated >= '".$yesterday."'			
			GROUP BY userid
 			"; //AND dcreated = '".$today."'
		// echo $query;	  AND dcreated != '".$yesterday."'
		
		$query = "
			SELECT 
				u.userid,
				COUNT(n.userid)  total
			FROM 
				[legrandv6].[dbo].[lfapUsers] as u
				LEFT JOIN [legrandv6].[dbo].[notes] as n ON u.userid = n.userid
			WHERE 
				n.typelu = '".$typelu."'
				AND n.dcreated >= '".$yesterday."'		
			GROUP BY
				u.userid		
		";
		$rs = $this->db->query($query);	
		$grab  = $rs->result_array();
		/*foreach($grab as $row){
				$array[$row["mgrid"]] = array(
					'total'		=>	$row["total"],
					'margin'	=>	$row["margin"],
				);	
			}
		*/
		return $grab;		
	}
	
	function dept_list(){
		$query = 
			"SELECT DISTINCT u.[department]
		  FROM [legrandv6].[dbo].[lfapUsers] as u
		  WHERE u.[department] <> ''
		 
		  "; // GROUP BY u.[department]
		return $this->db->query($query);
	}
	
	function user_list($dept = null){
		$query = 
			"SELECT *
			FROM [legrandv6].[dbo].[lfapUsers] as u
			WHERE   u.[isactive] = 1	";
		if($dept):
			$query .= "AND u.[department] = '".$dept."'";
		endif;
  			
		return $this->db->query($query);
	}
	
	function user_basic($userid=null){
		$query = "
			SELECT *
			FROM [legrandv6].[dbo].[lfapUsers] as u
			WHERE u.[userid] = '".$userid ."'";
			
		$rs = $this->db->query($query);
		
		return $rs;
	}
	
	function user_opportunities($userid=null){
	
	// needs cleaning up../ 
		$query = "
			SELECT *
			FROM [legrandv6].[dbo].[lfapUsers] as u
			
			
			JOIN [legrandv6].[dbo].[opportunities] as o
			ON o.mgrid = u.userid
		  
			JOIN [legrandv6].[dbo].[companies] as c on c.companyid = 	o.companyid
		  
			WHERE u.[userid] = '".$userid ."'
			AND   u.[isactive] = 1
			
			
			
			ORDER BY o.dcreated DESC
			
			";//WHERE u.[department] = 'Sales'
			
		$rs = $this->db->query($query);
		
		return $rs;
	}
	
	function opp_day_user($user = null, $day = null){
		$query ="
			SELECT COUNT(*) as total FROM [legrandv6].[dbo].[opportunities] as o
			JOIN [legrandv6].[dbo].[companies] as c on c.companyid = 	o.companyid
			WHERE  o.mgrid = '".$user."'
			AND o.dcreated > '26 Sep 2012' AND o.dcreated < '28 Sep 2012'
			";  //WHERE  o.mgrid = '".$user."'
			 
		$rs = $this->db->query($query);	
		return $rs;	
	}
	
	function business_closed($user = null, $services = null, $month = null){ 
	//business closed this month by a single user
		$month = date('m', time());
		$year = date('y', time());
		
		$last = date ('Y-m-d', mktime(0,0,0,$month,0,$year));
		
 		$query ="
			SELECT * 
			FROM [legrandv6].[dbo].[opportunities] as o
			JOIN [legrandv6].[dbo].[companies] as c on c.companyid = 	o.companyid
			WHERE o.mgrid = '".$user."'
			AND o.dactclose > '".$last."'
			AND o.dactclose != '".$last."'
			AND o.ioutcome = 1";
			
			if($services){
				$query.="
				AND o.oppuser01 = 'Services'
				";
			}	
			else{ 
				$query.="
				AND o.oppuser01 <> 'Services'
				";
			}		
		$rs = $this->db->query($query);	
		return $rs;	
		
	}
	
	
	function business_opened($user = null, $month = null){ 
	//business opened this month by a single user
		$month = date('m', time());
		$year = date('y', time());
		
		$last = date ('Y-m-d', mktime(0,0,0,$month,0,$year));
		
 		$query ="
			SELECT * 
			FROM [legrandv6].[dbo].[opportunities] as o
			JOIN [legrandv6].[dbo].[companies] as c on c.companyid = 	o.companyid
			WHERE o.mgrid = '".$user."'
			AND o.dcreated > '".$last."'
			AND o.dcreated != '".$last."'
			
			";
			 
		$rs = $this->db->query($query);	
		return $rs;	
		
	}
	function all_open($user = null){ 
	// all opps open for user
 		$query ="
			SELECT * 
			FROM [legrandv6].[dbo].[opportunities] as o
			JOIN [legrandv6].[dbo].[companies] as c on c.companyid = 	o.companyid
			WHERE o.mgrid = '".$user."'
			AND o.iactive = 1
			
			";
			 
		$rs = $this->db->query($query);	
		return $rs;	
		
	}
	function calls_today($user = null, $criteron = null){ 
	// all calls made today 
	// switch type, if there is one, else default to COLD CALL // _20111028113624SRKERHE
	if(!$criteron): 
		$typelu = '_20111028113624SRKERHE';
	else:
		switch($criteron){
			case 'cold':
				$typelu = '_20111028113624SRKERHE';
			break;
			case 'carebear':
				$typelu = '_20110926122458HKJJTBP';
			break;
			case 'cyberoam':
				$typelu = '_20110928093640UA5PVG1';
			break;
			case 'eshot':
				$typelu = '_20111104111945LLKERHE';
			break;
		}
	endif;
		$yesterday = date('d-M-Y',strtotime('-1 second'));
		 
 		$query ="
			SELECT 
				n.dcreated, 
				n.mnotes,
				n.dchanged,
				n.csummary,
				n.Status,
				c.ccyname			
				
			FROM [legrandv6].[dbo].[notes] as n
			JOIN [legrandv6].[dbo].[companies] as c on c.companyid = 	n.companyid
			WHERE n.typelu = '".$typelu."'
			AND n.dcreated > '".$yesterday."'
			AND n.dcreated != '".$yesterday."'
			AND n.userid = '".$user."'
			ORDER BY n.dcreated ASC
			";
			 
		$rs = $this->db->query($query);	
	 
		return $rs;	
		
	}

    function get_users($quantity)
    {
  

        $query = 
			"SELECT TOP ".$quantity."
			u.[ncolour]
			,u.[dchanged]
			,u.[dcreated]
			,u.[department]
			,u.[email]
			,u.[firstname]
			,u.[isactive]
			,u.[lastname]
			,u.[mobile]
			,u.[password]
			,u.[phone]
			,u.[position]
			,u.[teamid]
			,u.[title]
			,u.[usercode]
			,u.[userid]
			,u.[usersynctype]
			,u.[usertype]
			,o.moppdescr
		  FROM [legrandv6].[dbo].[lfapUsers] as u
		  
		  
		  
		  JOIN [legrandv6].[dbo].[opportunities] as o
			ON o.mgrid = u.userid
		  
		  WHERE u.[department] = 'Sales'
		  AND   u.[isactive] = 1
		  ";
		  
		  // doesnt seem to like active record due to back ticks...
		/* 
 		$this->db->select('*');
		$this->db->from('[dbo].[lfapUsers]');
		$this->db->limit(10);
		*/
		$rs = $this->db->query($query);
		//$rs = $this->db->get();
		
		return $rs;
    }

}