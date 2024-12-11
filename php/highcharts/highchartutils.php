<?php
use php\clases\Logger;
require_once("../clases/Logger.php");
//const EXPORT_HIGHCHARTS_SERVER= "http://13.57.25.243:8080/";
//const EXPORT_HIGHCHARTS_SERVER= "http://export.apps-handel.com:8080/";
//const EXPORT_HIGHCHARTS_SERVER= "https://export.apps-handel.com/";
//const EXPORT_HIGHCHARTS_SERVER = "https://export.highcharts.com/";


//FUNCIONAN:
//const EXPORT_HIGHCHARTS_SERVER = "http://export.highcharts.com/";
const EXPORT_HIGHCHARTS_SERVER= "http://18.144.27.6:8080/";

const SERVERS =  array("http://export.highcharts.com/", 
                    "http://export.apps-handel.com:8080"); 


function getHighchartsOptions($highchart)
{
    $data= (object) [
        'async' =>  true,
        'type' => 'image/jpeg',
        'width' => 1080,
        'options' => $highchart
    ];
    
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'content' => json_encode( $data ),
            'header'=>  "Content-Type: application/json\r\n" .
            "Accept: application/json\r\n"
        ),
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        )
    );
    return $options;
}

function getHightchartsURL($highchart)
{
    $charturl='';
    try 
    {
        $options = getHighchartsOptions($highchart);
        
        $url = EXPORT_HIGHCHARTS_SERVER;
        
        $context  = stream_context_create( $options );
        
        $result = file_get_contents( $url, false, $context );
        
       
        if ($result === FALSE)
        {
            
        }
        else
        {
            $charturl = $url . $result;
        }
        Logger::log("highcharts",$charturl);
        
    } 
    catch (Exception $e)
    {
        echo "error";
        Logger::log("highcharts",$e->getMessage());
    }
    return $charturl;
}

function getMes($mes)
{
  $nombre ="";
  switch ($mes)
  {
    case 1:
       $nombre = "Enero";
    break;
    case 2:
       $nombre = "Febrero";
    break;
    case 3:
       $nombre = "Marzo";
    break;
    case 4:
       $nombre = "Abril";
    break;
    case 5:
       $nombre = "Mayo";
    break;
    case 6:
       $nombre = "Junio";
    break;
    case 7:
       $nombre = "Julio";
    break;
    case 8:
       $nombre = "Agosto";
    break;
    case 9:
       $nombre = "Septiembre";
    break;
    case 10:
       $nombre = "Octubre";
    break;
    case 11:
       $nombre = "Noviembre";
    break;
    case 12:
       $nombre = "Diciembre";
    break;


  }
  return $nombre;

}

function getChartImage($type, $title, $yTitle, $serieTitle, $categories, $data)
{

  $highchart = (object)
      [
          'chart' => (object) [ 'type' => $type],
          'title' => (object) [ 'text'=> $title],
          'credits' => (object) ['enabled' => false],
          'xAxis' => (object) [ 'categories' => $categories],
          'plotOptions' => (object) ['column'=> (object)['dataLabels'=>(object)[ 'enabled'=>true, 'crop'=>false, 'overflow' =>'none' ]  ] ],
          'yAxis' => (object) [ 'title' => (object) [ 'text'=> $yTitle] , min=>0,max=>100,tickInterval=>10],
          'series' => array(
                      (object) ['name' => $serieTitle, 'data' => $data]
                    )
      ];

      $data= (object) [
        'async' =>  true,
        'type' => 'image/jpeg',
        'width' => 1080,
        'options' => $highchart
      ];

      $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => json_encode( $data ),
      'header'=>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n"
      )
    );

      $chartURL = getHightchartsURL($highchart);
      return $chartURL;

//  return 'ok';

}

function toColumnChart($title, $yTitle, $serieTitle, $rows, $xField, $yField,$colors, $showInLegend,$max)
{
    $categories = array();
    $data = array();
    
//     for ($i = 0; $i < count($rows); $i++) 
//     {
//         $row = $rows[$i];
//         $category = $row->$xField;
//         $value = $row->$yField;
//         array_push($categories, $category);
//         array_push($data, $value);
//     }
    $c = 0;
    for ($i = 0; $i < count($rows); $i++)
    {
        $row = $rows[$i];
        $category = $row->$xField;
        $value = (float)$row->$yField;
        
        if($value>=0 && $value<51)
            $color="#dd4b39";
        else if($value>=51 &&   $value <100)
            $color="#f39c12";
        else iF($value>=100)
            $color="#00a65a";
        
        
        
        $newRow= (object) [
            'name' =>  $category,
            'y' => $value,
            'color' => $color
        ];
        
        $c++;
        if($c>count($colors)-1)
            $c = 0;
        
        array_push($categories, $category);
        array_push($data, $newRow);
    }
    
    $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle]];
    if($max>0)
    {
        $yAxis->min= 0;
        $yAxis->max= $max;
        $yAxis->tickInterval= 10;
    }
        
        
    
    
    $highchart = (object)
    [
        'chart' => (object) [ 'type' => "column"],
        'title' => (object) [ 'text'=> $title],
        'credits' => (object) ['enabled' => false],
        'xAxis' => (object) [ 'categories' => $categories],
        'plotOptions' => (object) 
        [
            'column'=> (object)[
                   'dataLabels'=>(object)
                    [ 
                        'enabled'=>true, 
                        'crop'=>false, 
                        'overflow' =>'none',
                        "inside"=> true,
                        'color'=> 'black',
                        'style'=> (object)
                        [
                            'fontSize' => 10,
                            'textOutline' => '0px'
                        ]
                    ]
            ] 
        ],
        'yAxis' => $yAxis,
        'series' => array(
            (object) ['name' => $serieTitle, 'data' => $data,  'showInLegend' => $showInLegend]
        )
    ];
    
    $chartURL = getHightchartsURL($highchart);
    return $chartURL;
    
    //  return 'ok';
    
}


function toColumnChartSerieColors($title, $yTitle, $serieTitle, $rows, $xField, $yField,$colors, $showInLegend,$max,$inside=true,$format="{point.y}")
{
    $categories = array();
    $data = array();
    
    //     for ($i = 0; $i < count($rows); $i++)
        //     {
        //         $row = $rows[$i];
        //         $category = $row->$xField;
        //         $value = $row->$yField;
        //         array_push($categories, $category);
        //         array_push($data, $value);
        //     }
    $c = 0;
    for ($i = 0; $i < count($rows); $i++)
    {
        $row = $rows[$i];
        $category = $row->$xField;
        $value = (float)$row->$yField;
        $color = $colors[$c];
        
//         if($value>=0 && $value<51)
//             $color="#dd4b39";
//             else if($value>=51 &&   $value <100)
//                 $color="#f39c12";
//                 else iF($value>=100)
//                     $color="#00a65a";
                    
                    
                    
        $newRow= (object) [
            'name' =>  $category,
            'y' => $value,
            'color' => $color
        ];
        
        $c++;
        if($c>count($colors)-1)
            $c = 0;
                        
        array_push($categories, $category);
        array_push($data, $newRow);
    }
    
    $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle]];
    if($max>0)
    {
        $yAxis->min= 0;
        $yAxis->max= $max;
        $yAxis->tickInterval= 10;
    }
    
    
    
    
    $highchart = (object)
    [
        'chart' => (object) [ 'type' => "column"],
        'title' => (object) [ 'text'=> $title],
        'credits' => (object) ['enabled' => false],
        'xAxis' => (object) [ 'categories' => $categories],
        'plotOptions' => (object)
        [

            'series' => (object)
            [
                'dataLabels'=>(object)
                [
                    'enabled'=>true,
                    'format' => $format,
                    'color'=> 'black',
                    'style'=> (object)
                    [
                        'fontSize' => 10,
                        'textOutline' => '0px'
                    ],
                ]
            ]
        ],
        'yAxis' => $yAxis,
        'series' => array(
            (object) ['name' => $serieTitle, 'data' => $data,  'showInLegend' => $showInLegend]
        )
    ];
    
    $chartURL = getHightchartsURL($highchart);
    return $chartURL;
    
    //  return 'ok';
    
}

function toColumnChartColors($title, $yTitle, $serieTitle, $rows, $xField, $yField,$colors, $showInLegend,$max)
{
    $categories = array();
    $data = array();
    
    //     for ($i = 0; $i < count($rows); $i++)
        //     {
        //         $row = $rows[$i];
        //         $category = $row->$xField;
        //         $value = $row->$yField;
        //         array_push($categories, $category);
        //         array_push($data, $value);
        //     }
    $c = 0;
    $series = array();
    for ($i = 0; $i < count($rows); $i++)
    {
        $row = $rows[$i];
        $category = $row->$xField;
        $value = (float)$row->$yField;
        
//         if($value>=0 && $value<51)
//             $color="#dd4b39";
//         else if($value>=51 &&   $value <100)
//             $color="#f39c12";
//         else iF($value>=100)
//             $color="#00a65a";
            
        $color = $colors[$c];
            
        $newRow= (object) [
            'name' =>  $category,
            'y' => $value,
            'color' => $color
        ];
        
        $c++;
        if($c>count($colors)-1)
            $c = 0;
            
        array_push($categories, $category);
        $data = array();
        array_push($data, $newRow);
        
        $serie = (object) ['name' => $category, 'data' => $data,  'showInLegend' => true, 'color' => $color];
        array_push($series, $serie);
    }
    
    $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle]];
    if($max>0)
    {
        $yAxis->min= 0;
        $yAxis->max= $max;
        $yAxis->tickInterval= 10;
    }
    
    
    
    
    $highchart = (object)
    [
        'chart' => (object) [ 'type' => "column"],
        'title' => (object) [ 'text'=> $title],
        'credits' => (object) ['enabled' => false],
        'xAxis' => (object) [ 'categories' => array([$yTitle])],
        'plotOptions' => (object)
        [
            'column'=> (object)[
                'dataLabels'=>(object)
                [
                    'enabled'=>true,
                    'crop'=>false,
                    'overflow' =>'none',
                    "inside"=> false,
                    'color'=> 'black',
                    'style'=> (object)
                    [
                        'fontSize' => 10,
                        'textOutline' => '0px'
                    ]
                ]
            ]
        ],
        'yAxis' => $yAxis,
        'series' =>         $series
            //array(            (object) ['name' => $serieTitle, 'data' => $data,  'showInLegend' => $showInLegend]        )
    ];
    
    $chartURL = getHightchartsURL($highchart);
    return $chartURL;
    
    //  return 'ok';
    
}

function toLineChart($title, $yTitle, $serieTitle, $rows, $xField, $yField,$colors, $showInLegend,$max,$mes)
{
    $categories = array();
    $data = array();
    
    $data = array();
    
    $data1 = array();
    $data2 = array();
    $data3 = array();
    $data4 = array();
    
    $fecha = new DateTime();
    $mesActual = (int)$fecha->format("m");

    for ($i = 0; $i < count($rows); $i++)
    {
        $row = $rows[$i];
        
        $newRow= (object) [
            'name' =>  $row->$xField,
            'y' => (float)$row->$yField,
            //'color' => "#005493" 
            
        ];
        
        $newRow1= (object) [
            'name' =>  $row->$xField,
            'y' => 85,
            //'color' => "#00a65a"
        ];
        
        $newRow2= (object) [
            'name' =>  $row->$xField,
            'y' => 70,
           // 'color' => "#f39c12"
            
        ];
        
        $newRow3= (object) [
            'name' =>  $row->$xField,
            'y' => 50,
           // 'color' => "#dd4b39"
            
            
        ];
        
        $newRow4= (object) [
            'name' =>  $row->$xField,
            'y' => (float)$row->porcentajeCumplimientoEnviadas,
           // 'color' => "#00a1ff"
            
            
        ];
        
        array_push($categories, $row->$xField);
        if($i<=$mes-1)
        {
            if($newRow4->y!=null)
            {
                array_push($data, $newRow);
                array_push($data4, $newRow4);
            }
        }
        array_push($data1, $newRow1);
        array_push($data2, $newRow2);
        array_push($data3, $newRow3);
    }
    
    $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle]];
    if($max>0)
    {
        $yAxis->min= 0;
        $yAxis->max= $max;
        $yAxis->tickInterval= 10;
    }
    
    
    
    
    $highchart = (object)
    [
        'chart' => (object) [ 'type' => "line"],
        'title' => (object) [ 'text'=> $title],
        'credits' => (object) ['enabled' => false],
        'xAxis' => (object) [ 'categories' => $categories],
        'yAxis' => $yAxis,
        'series' => array(
            (object) ['name' => "Riesgo mínimo", 'data' => $data,  'showInLegend' => $showInLegend,  
                'dataLabels'=>(object)
                [
                    'enabled'=>true,
                    'style'=> (object)
                    [
                        'fontSize' => 10,
                        'textOutline' => '0px'
                    ],
                    'verticalAlign' => 'bottom',
                    'y' => -20
                ],"color"=>"#005493"],
            
            (object) ['name' => "Riesgo bajo", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#60d836"],
            (object) ['name' => "Riesgo medio", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#f9c320"],
            (object) ['name' => "Riesgo alto", 'data' => $data3,  'showInLegend' => $showInLegend, "color"=>"#fe2500"],
            (object) ['name' => "Riesgo máximo", 'data' => $data4,  'showInLegend' => $showInLegend,  
                'dataLabels'=>(object)
                [
                    'enabled'=>true,
                    'style'=> (object)
                    [
                        'fontSize' => 10,
                        'textOutline' => '0px'
                    ],
                    'verticalAlign' => 'top'
                ], "color" => "#00a1ff"]
        )
    ];
    
    $chartURL = getHightchartsURL($highchart);
    return $chartURL;
    
    //  return 'ok';
    
}

function toPieChart( $title, $yTitle, $serieTitle, $rows, $xField, $yField, $colors,$distance=-50)
{
    $categories = array();
    $data = array();
    
    for ($i = 0; $i < count($rows); $i++)
    {
        $row = $rows[$i];
        $category = $row->$xField;
        $value = $row->$yField;
        
        $newRow= (object) [
            'name' =>  $category,
            'y' => $value
        ];
        
        //array_push($categories, $category);
        array_push($data, $newRow);
    }
    
    $highchart = (object)
    [
        'chart' => (object) [ 'type' => "pie"],
        'title' => (object) [ 'text'=> $title],
        'credits' => (object) ['enabled' => false],
        'plotOptions' => (object) 
        [
            'pie'=> (object)
            [
                'dataLabels'=>(object)
                [
                    'distance'=> $distance,
                    'color'=> 'black',
                    'style'=> (object)
                     [
                         'fontSize' => 10,
                         'textOutline' => '0px'
                         
                     ], 
                    'enabled'=>true, 
                    'format'=>"{point.percentage:.1f} %" 
                ] ,
                "colors"  => $colors,
                'showInLegend' => true
            ],
            
            
        ],
        'series' => array(
            (object) ['name' => $serieTitle, 'colorByPoint'=> true, 'data' => $data]
        )
    ];
    
    $chartURL = getHightchartsURL($highchart);
    return $chartURL;
    
    //  return 'ok';
    
}


function toPieChartWithLabels( $title, $yTitle, $serieTitle, $rows, $xField, $yField, $colors,$distance=50)
{
    $categories = array();
    $data = array();
    
    for ($i = 0; $i < count($rows); $i++)
    {
        $row = $rows[$i];
        $category = $row->$xField;
        $value = $row->$yField;
        
        $newRow= (object) [
            'name' =>  $category,
            'y' => $value
        ];
        
        //array_push($categories, $category);
        array_push($data, $newRow);
    }
    
    $highchart = (object)
    [
        'chart' => (object) [ 'type' => "pie"],
        'title' => (object) [ 'text'=> $title],
        'credits' => (object) ['enabled' => false],
        'plotOptions' => (object)
        [
            'pie'=> (object)
            [
                'dataLabels'=>(object)
                [
                    'distance'=> $distance,
                    'color'=> 'black',
                    'style'=> (object)
                    [
                        'fontSize' => 10,
                        'textOutline' => '0px'
                        
                    ],
                    'enabled'=>true,
                    'format'=>"<b>{point.name}</b>: {point.percentage:.1f} %"
                ] ,
                "colors"  => $colors,
                'showInLegend' => true
            ],
            
            
        ],
        'series' => array(
            (object) ['name' => $serieTitle, 'colorByPoint'=> true, 'data' => $data]
        )
    ];
    
    $chartURL = getHightchartsURL($highchart);
    return $chartURL;
    
    //  return 'ok';
    
}

function getImage($highchart)
{
    $dataHighChart= (object) [
      'async' =>  true,
      'type' => 'image/jpeg',
      'width' => 1080,
      'options' => $highchart
    ];

    $options = array(
  'http' => array(
    'method'  => 'POST',
    'content' => json_encode( $dataHighChart ),
    'header'=>  "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n"
    )
  );
  
 

  $url = EXPORT_HIGHCHARTS_SERVER;

  $context  = stream_context_create( $options );
  $result = file_get_contents( $url, false, $context );

  
  
  $charturl='';
  if ($result === FALSE)
  {

  }
  else
  {
    $charturl = $url . $result;

  }
 
  return $charturl;
}
?>