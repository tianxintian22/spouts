<?php

leaveAddSave();
function leaveAddSave()
    {
         
        $starttime = '2017-06-30 18:30:00';
        $endtime = '2017-07-03 07:40:00';
        
        $param = array();
        
        $workTime = getAttendTime();
        $rtnDay = getLeaveDay($starttime, $endtime);
        $leave_day = timediff($rtnDay['starttime'], $rtnDay['endtime']);
        $param['leave_day'] = $leave_day['day'] . '天'. $leave_day['hour'] . '小时' . $leave_day['min'] . '分钟';
        echo $param['leave_day'];

    }

    function getLeaveDay($starttime, $endtime)
    {
        $workTime = getAttendTime();
        $endtime1 = strtotime(date('H:i:s', strtotime($endtime)));
        $starttime1 = strtotime(date('H:i:s', strtotime($starttime)));
        $sflag = 'am';
        $eflag = 'pm';
        $flag = 0;
        
        if ($endtime1 > strtotime($workTime[0]['worktime_4']))
        {
            $endtime = date('Y-m-d', strtotime($endtime)).$workTime[0]['worktime_4'];
            $eflag = 'pm';
        }
        else if($endtime1 <= strtotime($workTime[0]['worktime_3']) && $endtime1 >= strtotime($workTime[0]['worktime_2']))
        {
            $endtime = date('Y-m-d', strtotime($endtime)).$workTime[0]['worktime_3'];
            $eflag = 'pm';
        }
        else if($endtime1 <= strtotime($workTime[0]['worktime_4']) && $endtime1 > strtotime($workTime[0]['worktime_3']))
        {
            $eflag = "pm";
        }
        else if($endtime1 < strtotime($workTime[0]['worktime_2']) && $endtime1 >= strtotime($workTime[0]['worktime']))
        {
            $eflag = 'am';
        }
        else if($endtime1 < strtotime($workTime[0]['worktime']))
        {
            $endtime = date('Y-m-d', strtotime($endtime)).$workTime[0]['worktime'];
            $eflag = 'am';
        }
        
        if ($starttime1 < strtotime($workTime[0]['worktime']))
        {
            $starttime = date('Y-m-d', strtotime($starttime)).$workTime[0]['worktime'];
            $sflag = 'am';
        }
        else if ($starttime1 >= strtotime($workTime[0]['worktime_2']) && $starttime1 <= strtotime($workTime[0]['worktime_3']))
        {
            $starttime = date('Y-m-d', strtotime($starttime)).$workTime[0]['worktime_2'];
            $sflag = 'am';
        }
        else if ($starttime1 >= strtotime($workTime[0]['worktime']) && $starttime1 < strtotime($workTime[0]['worktime_2']))
        {
            $sflag = "am";
        }
        else if ($starttime1 > strtotime($workTime[0]['worktime_3']) && $starttime1 <= strtotime($workTime[0]['worktime_4']))
        {
            $sflag = "pm";
        }
        else if ($starttime1 > strtotime($workTime[0]['worktime_4']))
        {
            $starttime = date('Y-m-d', strtotime($starttime)).$workTime[0]['worktime_4'];
            $sflag = "pm";
        }
        //开始时间大于结束时间（时分秒）
        if (strtotime(date('H:i:s', strtotime($starttime))) > strtotime(date('H:i:s', strtotime($endtime))))
        {
        	$flag = 1;
        }
        
        $noonTimestamp = strtotime($workTime[0]['worktime_3']) - strtotime($workTime[0]['worktime_2']);
        $eveningTimestamp = 24*3600 - (strtotime($workTime[0]['worktime_4']) - strtotime($workTime[0]['worktime']));
        
        if ($flag && (($eflag  == 'pm' && $sflag == 'pm') || ($eflag == 'am' && $sflag == 'am')))
        {
            $endtimeStamp = strtotime($endtime) - $eveningTimestamp - $noonTimestamp;
        }
        else if ($flag)
        {
        	$endtimeStamp = strtotime($endtime) - $eveningTimestamp;
        }
        else if(($sflag != $eflag) && $flag == 0)
        {
            $endtimeStamp = strtotime($endtime) - $noonTimestamp;
        }
        else 
        {
            $endtimeStamp = strtotime($endtime);
        }

        $starttimeStamp = strtotime($starttime);

        return array(
            'starttime' => $starttimeStamp,
            'endtime' => $endtimeStamp  
        );
    }

    function getAttendTime()
    {
    	return array('0'=> array(
    		'worktime' => '08:30:00',
    		'worktime_2' => '12:00:00',
    		'worktime_3' => '13:30:00',
    		'worktime_4' => '17:30:00'
    		)
    		);
    }

    function timediff($begin_time,$end_time)
    {
        if($begin_time < $end_time)
        {
            $starttime = $begin_time;
            $endtime = $end_time;
        }
        else
        {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        
        //计算天数
        $timediff = $endtime - $starttime;
        $days = intval($timediff / 86400);
        //计算小时数
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        //计算分钟数
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        //计算秒数
        $secs = $remain % 60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }
?> 
