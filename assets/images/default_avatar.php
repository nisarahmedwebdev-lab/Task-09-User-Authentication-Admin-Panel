<?php
// Create a simple default avatar image
header('Content-Type: image/png');

// Create image
$width = 200;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg_color = imagecolorallocate($image, 52, 152, 219); // Blue background
$text_color = imagecolorallocate($image, 255, 255, 255); // White text

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Draw a circle for the user icon
$circle_color = imagecolorallocate($image, 255, 255, 255);
imagefilledellipse($image, 100, 80, 80, 80, $circle_color);

// Draw a body
imagefilledellipse($image, 100, 160, 100, 60, $circle_color);

// Draw text "User"
$text = "U";
$font_size = 80;
// Use built-in font
imagestring($image, 5, 85, 70, $text, $text_color);

// Output image
imagepng($image);
imagedestroy($image);
?>