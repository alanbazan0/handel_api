<?php 
include '../clases/Maps.php';



$url = "https://maps.googleapis.com/maps/api/staticmap?zoom=13&size=400x200&maptype=roadmap&markers=color%3Ared%7Clabel%3AUbicaci%C3%B3n%7C25.74518229068739%2C-100.52451133727938&key=AIzaSyD-Od_H6umzU60EXytVi2WnB6Y0lwcVyWo";

$resultado = Maps::getMap($url);
var_dump($resultado);
