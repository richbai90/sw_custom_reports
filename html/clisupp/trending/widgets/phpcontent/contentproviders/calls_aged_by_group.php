<?php

//-- we have xmlc and sqlquery to hand so we can do a sql statement and construct fusion chart data set

/*
 * This is the click action, it's what allow you to open a call from the dashboard data. Leave it.
 */
function hslanchor_opencall($cr)
{
    return "<a href='#' onclick='run_hsl_anchor(this)' atype='calldetails' key='" . $cr . "'>" . $cr . "</a>";
}


//We are going to use just one query object, we only actually need one. We will run each query in synchronous
//If we had the capacity of running each one on its own thread, multiple query objects would be benneficial

$rs = new SqlQuery();

$strSql = <<<sql
SELECT oc1.suppgroup,
count(oc2.callref) as lt10,
count(oc3.callref) as lt20,
count(oc4.callref) as lt30,
count(oc5.callref) as lt60,
count(oc6.callref) as lt90,
count(oc7.callref) as lt120,
count(oc8.callref) as gt120


from opencall oc1
left join opencall oc2 on oc1.callref = oc2.callref and oc2.logdatex >= UNIX_TIMESTAMP(CURDATE()) - 28800
left join opencall oc3 on oc1.callref = oc3.callref and oc3.logdatex > UNIX_TIMESTAMP(CURDATE()) - 57600 and oc3.logdatex < UNIX_TIMESTAMP(CURDATE()) - 28800
left join opencall oc4 on oc1.callref = oc4.callref and oc4.logdatex > UNIX_TIMESTAMP(CURDATE()) - 86400 and oc4.logdatex < UNIX_TIMESTAMP(CURDATE()) - 57600
left join opencall oc5 on oc1.callref = oc5.callref and oc5.logdatex > UNIX_TIMESTAMP(CURDATE()) - 172800 and oc5.logdatex < UNIX_TIMESTAMP(CURDATE()) - 86400
left join opencall oc6 on oc1.callref = oc6.callref and oc6.logdatex > UNIX_TIMESTAMP(CURDATE()) - 259200 and oc6.logdatex < UNIX_TIMESTAMP(CURDATE()) - 172800
left join opencall oc7 on oc1.callref = oc7.callref and oc7.logdatex > UNIX_TIMESTAMP(CURDATE()) - 345600 and oc7.logdatex < UNIX_TIMESTAMP(CURDATE()) - 259200
left join opencall oc8 on oc1.callref = oc8.callref and oc8.logdatex  < UNIX_TIMESTAMP(CURDATE()) - 345600

group by oc1.suppgroup
sql;


//What we intend to get

/*<table>
    <tr class='tbl-h'>
        <td>support group</td>
        <td>0-10</td>
        <td>10-20</td>
        <td>20-30</td>
        <td> > 30</td>
    </tr>
    <tr class = 'tr-odd'>
        <td>support</td> // support group
        <td>20</td> // 0 - 10
        <td>5</td>  // 10 - 20
        <td>0</td>  // 20 - 30
        <td>15</td> // > 30
    </tr>
</table> */

//setup the even/odd scheme
$x = 1;

//query the record and build the table
$rs->Query($strSql);
while ($rs->Fetch()) {
    $evenOdd .= ($x % 2 == 0) ? '' : 'class="tr-odd"';
    $suppgroup = $rs->GetValueAsString('suppgroup');
    $lt10 = $rs->GetValueAsNumber('lt10');
    $lt20 = $rs->GetValueAsNumber('lt20');
    $lt30 = $rs->GetValueAsNumber('lt30');
    $lt60 = $rs->GetValueAsNumber('lt60');
    $lt90 = $rs->GetValueAsNumber('lt90');
    $lt120 = $rs->GetValueAsNumber('lt120');
    $gt120 = $rs->GetValueAsNumber('gt120');

    $tableData .= <<<html
    <tr $evenOdd>
        <td>$suppgroup</td>
        <td>$lt10</td>
        <td>$lt20</td>
        <td>$lt30</td>
        <td>$lt60</td>
        <td>$lt90</td>
        <td>$lt120</td>
        <td>$gt120</td>
    </tr>
html;
    $x++;
}


//build the table
$table = <<<html
<table cellspacing="0" cellpadding="2">
    <tr class="tbl-h">
        <td>Support Group</td>
        <td>0 - 10</td>
        <td>10 - 20</td>
        <td>20 - 30</td>
        <td>30 - 60</td>
        <td>60 - 90</td>
        <td>90 - 120</td>
        <td>Greater than 120</td>
    </tr>
    $tableData
</table>
html;


echo $table;


