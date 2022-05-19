<!doctype html>
<html lang="en">
<head>
    <title>phpColors Demo</title>
    <?php 
namespace WP_Ultimo\Dependencies;

require_once __DIR__ . '/../src/Mexitek/PHPColors/Color.php';
use WP_Ultimo\Dependencies\Mexitek\PHPColors\Color;
// Use different colors to test
$myBlue = new Color("#336699");
$myBlack = new Color("#333");
$myPurple = new Color("#913399");
$myVintage = new Color("#bada55");
// ************** No Need to Change Below **********************
?>
    <style>
        .block {
            height: 100px;
            width: 200px;
            font-size: 20px;
            text-align: center;
            padding-top: 100px;
            display: block;
            margin: 0;
            float: left;
        }

        .wide-block {
            width: 360px;
            padding-top: 70px;
            padding-left: 20px;
            padding-right: 20px;
            margin-top: 10px;
        }

        .clear {
            clear: both;
        }

        .testDiv {
            <?php 
echo $myBlue->getCssGradient();
?>
            color: <?php 
echo $myBlue->isDark() ? "#EEE" : "#333";
?>;
        }

        .testDiv.plain {
            background: #<?php 
echo $myBlue->getHex();
?>;
            color: <?php 
echo $myBlue->isDark() ? "#EEE" : "#333";
?>;
        }

        .testDiv2 {
            <?php 
echo $myBlack->getCssGradient();
?>
            color: <?php 
echo $myBlack->isDark() ? "#EEE" : "#333";
?>;
        }

        .testDiv2.plain {
            background: #<?php 
echo $myBlack->getHex();
?>;
            color: <?php 
echo $myBlack->isDark() ? "#EEE" : "#333";
?>;
        }

        .testDiv3 {
            <?php 
echo $myPurple->getCssGradient();
?>
            color: <?php 
echo $myPurple->isDark() ? "#EEE" : "#333";
?>;
        }

        .testDiv3.plain {
            background: #<?php 
echo $myPurple->getHex();
?>;
            color: <?php 
echo $myPurple->isDark() ? "#EEE" : "#333";
?>;
        }

        .testDiv4 {
            <?php 
echo $myVintage->getCssGradient(30, \true);
?>
            color: <?php 
echo $myVintage->isDark() ? "#EEE" : "#333";
?>;
        }
    </style>
</head>
<body>
<div class="clear"></div>
<div class="block testDiv">phpColor Gradient #<?php 
echo $myBlue->getHex();
?></div>
<div class="block testDiv plain">Plain #<?php 
echo $myBlue->getHex();
?></div>
<div class="clear"></div>
<div class="block testDiv2">phpColor Gradient #<?php 
echo $myBlack->getHex();
?></div>
<div class="block testDiv2 plain">Plain #<?php 
echo $myBlack->getHex();
?></div>
<div class="clear"></div>
<div class="block testDiv3">phpColor Gradient #<?php 
echo $myPurple->getHex();
?></div>
<div class="block testDiv3 plain">Plain #<?php 
echo $myPurple->getHex();
?></div>
<div class="clear"></div>
<div class="block wide-block testDiv4">
    phpColor Gradient with vintage browsers support #<?php 
echo $myVintage->getHex();
?>
</div>
</body>
</html>
<?php 
