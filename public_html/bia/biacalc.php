<?php

/* BIA Calculator Prototype for City of Santa Cruz, CA
*
*  developer: jim@codeforamerica.org
*  owner: pkoht@cityofsantacruz.com
*  ref: http://www.cityofsantacruz.com/Modules/ShowDocument.aspx?documentid=14546
*
*/

// Define Address::Zone tables

//zone_table_basic is for simple address/zone rules:
//we just need street_num and street_name

$zone_table_basic = array
(
array("street"=>"bulkhead-st", "min"=>100, "max"=>199, "zone"=>2),
array("street"=>"cathcart-st", "min"=>100, "max"=>299, "zone"=>1),
array("street"=>"cedar-st", "min"=>300, "max"=>399, "zone"=>2),
array("street"=>"cedar-st", "min"=>400, "max"=>1199, "zone"=>1),
array("street"=>"center-st", "min"=>300, "max"=>1099, "zone"=>2),
array("street"=>"church-st", "min"=>100, "max"=>199, "zone"=>1),
array("street"=>"church-st", "min"=>200, "max"=>299, "zone"=>2),
array("street"=>"commerce-ln", "min"=>1300, "max"=>1399, "zone"=>1),
array("street"=>"cooper-st", "min"=>100, "max"=>199, "zone"=>1),
array("street"=>"elm-st", "min"=>100, "max"=>199, "zone"=>1),
array("street"=>"elm-st", "min"=>200, "max"=>299, "zone"=>2),
array("street"=>"front-st", "min"=>300, "max"=>407, "zone"=>2),
array("street"=>"front-st", "min"=>531, "max"=>899, "zone"=>1),
array("street"=>"knight-st", "min"=>100, "max"=>199, "zone"=>2),
array("street"=>"laurel-st", "min"=>100, "max"=>399, "zone"=>2),
array("street"=>"lincoln-st", "min"=>100, "max"=>199, "zone"=>1),
array("street"=>"lincoln-st", "min"=>200, "max"=>304, "zone"=>2),
array("street"=>"locust-st", "min"=>100, "max"=>199, "zone"=>1),
array("street"=>"locust-st", "min"=>200, "max"=>299, "zone"=>2),
array("street"=>"maple-st", "min"=>200, "max"=>299, "zone"=>2),
array("street"=>"mission-st", "min"=>100, "max"=>120, "zone"=>2),
array("street"=>"north-pacific-av", "min"=>2000, "max"=>2099, "zone"=>2),
array("street"=>"pacific-av", "min"=>715, "max"=>900, "zone"=>2),
array("street"=>"pacific-av", "min"=>905, "max"=>1599, "zone"=>1),
array("street"=>"palomar-ar", "min"=>1, "max"=>99, "zone"=>1),
array("street"=>"pearl-al", "min"=>100, "max"=>199, "zone"=>2),
array("street"=>"river-st", "min"=>10, "max"=>75, "zone"=>1),
array("street"=>"river-st", "min"=>76, "max"=>199, "zone"=>2),
array("street"=>"river-street-south", "min"=>100, "max"=>199, "zone"=>1),
array("street"=>"soquel-av", "min"=>100, "max"=>199, "zone"=>1),
array("street"=>"union-st", "min"=>100, "max"=>199, "zone"=>2),
array("street"=>"walnut-av", "min"=>100, "max"=>125, "zone"=>1),
array("street"=>"walnut-av", "min"=>132, "max"=>199, "zone"=>2),
array("street"=>"water-st", "min"=>100, "max"=>149, "zone"=>2)
);

//zone_table_complex is for complex address/zone rules based on side of street
//so we'll do an even/odd test on street_num and assign it a side.

$zone_table_complex = array
(
array("street"=>"maple-st", "min"=>100, "max"=>198, "side"=>"even", "zone"=>1),
array("street"=>"maple-st", "min"=>101, "max"=>199, "side"=>"odd", "zone"=>2),
array("street"=>"front-st", "min"=>408, "max"=>530, "side"=>"even", "zone"=>2),
array("street"=>"front-st", "min"=>409, "max"=>529, "side"=>"odd", "zone"=>1)
);

//zone_table_exception is for one-offs that require unique logic

$zone_table_exception = array
(
array("street"=>"pacific-av", "num"=>901, "zone"=>1),
array("street"=>"pacific-av", "num"=>902, "zone"=>2),
array("street"=>"pacific-av", "num"=>903, "zone"=>1),
array("street"=>"pacific-av", "num"=>904, "zone"=>2)
);

//let's create a web page because we're going to start educating this user...
?>

<html>
<head>
	<title>BIA Calculator Results</title>
	<link href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<div id="header">
<h2>City of Santa Cruz: BIA Calculator Results</h2>
</div>
<div id="confirm">
<?php

//get user-submitted values.  validate later.

$biz_area = $_POST['biz_area'];
$biz_type = $_POST['biz_type'];

//attempting to get street number and address from system
$street = $_POST['street_num_and_name'];

$street = trim( $street );
$street_num = explode( ' ', $street, 2 );
$street_num = $street_num[0] * 1;
$street_name = explode( ' ', $street, 2 );
$street_name = str_replace( " ", "-", strtolower( $street_name[1] ) );
$street_name = str_replace( "street", "st", $street_name );
$street_name = str_replace( "avenue", "av", $street_name );
$street_name = str_replace( "ave", "av", $street_name );

// let's validate the business area is a number and make sure it's under our cap (10,000sqft)

if (is_numeric($biz_area)) {
  if ($biz_area > 10000) {
    $biz_area = 10000;
    echo "<p>We cap the business area for this calculation at 10,000 square feet.  We'll be using that size for the rest of our calculations.</p>";
  }
}
else {
  echo "<p>You submitted \"$biz_area\" for your business area in square feet.  Please enter a whole number with no letters or punctuation.<br />";
  echo "<a href=\"index.html\">Click here to return to the calculator.</a></p>";
}

echo "<p>Using <strong>$biz_area square feet</strong> for calculations...</p>";

// let's confirm the Business Type that the user selected and calculate their BizType Rate

echo "<p>You selected <strong>Business Type $biz_type</strong>.</p>";

switch ($biz_type) {

  case 1:
    $biz_type_rate = 1.0;
    echo "<p>Business Type $biz_type businesses (Retail and Food) are assessed at 100% (see below).</p>";
    break;
  case 2:
    $biz_type_rate = 0.6;
    echo "<p>Business Type $biz_type businesses (Financial, Bars, and Theaters) are assessed at 60% (see below).</p>";
    break;
  case 3:
    $biz_type_rate = 0.4;
    echo "<p>Business Type $biz_type businesses (Wholesale Trade, Services, Professions, Auto Dealers) are assessed at 40% (see below).</p>";
    break;
}

// let's validate the street_num that the user entered.

if (!is_numeric($street_num)) {
echo "<p>You entered $street_num as a Street Number.  Please enter a whole number with no letters or punctuation.<br />";
echo "<a href=\"index.html\">Click here to return to the calculator.</a></p>";
}

// let's make a human-friendly version of the street name to display to the user

$street_name_human = str_replace('-', ' ', $street_name);
$street_name_human = ucwords($street_name_human);

// let's confirm the user's address

echo "<p>You entered your business address as <strong>$street_num $street_name_human</strong>.</p>";

// OK, let's try to plug through this address::zone logic.

$biz_zone = 0;

// first let's see if it's in the zone_table_basic

$wctr = 0;

while (($biz_zone == 0) && ($wctr <= count($zone_table_basic)))
{

  if ($zone_table_basic[$wctr]['street'] == $street_name &&
      $street_num >= $zone_table_basic[$wctr]['min'] &&
      $street_num <= $zone_table_basic[$wctr]['max'])
  {
    $biz_zone = $zone_table_basic[$wctr]['zone'];
  }
  $wctr++;
}

// if biz_zone is still 0, we need to check zone_table_complex
// zone_table_complex requires side of street so we need to see if street_num is even or odd.

if ($street_num % 2) $street_side = "odd"; else $street_side = "even";

$wctr = 0;
while ($biz_zone == 0 && $wctr <= count($zone_table_complex)) {
  if ($zone_table_complex[$wctr]['street'] == $street_name &&
      $street_num >= $zone_table_complex[$wctr]['min'] &&
      $street_num <= $zone_table_complex[$wctr]['max'] &&
      $street_side == $zone_table_complex[$wctr]['side']
      ) {
    $biz_zone = $zone_table_complex[$wctr]['zone'];
}
  $wctr++;
}

// if biz_zone is still 0, let's see if it matches our one-offs in the _exception table.

$wctr = 0;
while ($biz_zone == 0 && $wctr <= count($zone_table_exception)) {
  if ($zone_table_exception[$wctr]['street'] == $street_name &&
      $zone_table_exception[$wctr]['num'] == $street_num
      ) {
    $biz_zone = $zone_table_exception[$wctr]['zone'];
  }
  $wctr++;
}

// if biz_zone is still 0, they are not in the BIA zone so send them packing.

if ($biz_zone == 0) {
echo "<p><strong>It appears that you are not in the Business Improvement Assessment zone.</strong>  If you feel this is incorrect,
please contact the Downtown Association at (831) 429-8433 or the City Finance Department at
(831) 420-5072.  We will be happy to answer any questions you may have.</p>";
}

// but for the folks in the biz zone, let's tell them their zone and rate before we start doing calculations.

else {
  echo "<p>You are in BIA Zone $biz_zone.</p>";

switch ($biz_zone) {
  case 1:
    $biz_zone_rate = 1.0;
    echo "<p>BIA Zone 1 Assessment level is 100%</p>";
    break;
  case 2:
    $biz_zone_rate = 0.6;
    echo "<p>BIA Zone 2 Assessment level is 60%</p>";
    break;
}

echo "</div><div id=\"calculation\"><hr><h3>Calculation</h3>";

echo "<p>The BIA is computed with the following equation:
(square footage x 0.32 x Business Type Rate x BIA Zone Rate ) + $50</p>";

echo "<p>Square footage of $biz_area square feet<br />
x&nbsp;&nbsp;0.32<br />
x&nbsp;&nbsp;Business Type Rate $biz_type_rate<br />
x&nbsp;&nbsp;Business Zone Rate $biz_zone_rate<br />
+&nbsp;&nbsp;$50</p>";

$bia_annual_raw = ($biz_area * 0.32 * $biz_type_rate * $biz_zone_rate) + 50;
$bia_annual_human = number_format($bia_annual_raw, 2);

echo "<p>Annual BIA is $$bia_annual_human.</p>";

$bia_semi_human = number_format(($bia_annual_raw/2), 2);

echo "<p>Semi-annual BIA is $$bia_semi_human.</p>";

echo "<p>Questions about BIA?  Please contact the Downtown Association at (831) 429-8433 or the City Finance Department at
(831) 420-5072.  We will be happy to answer any questions you may have.</p>";

}
//
//let's wrap up our page now.
?>
</div></body></html>

<?php

//anything else to do, trigger, etc.?

?>