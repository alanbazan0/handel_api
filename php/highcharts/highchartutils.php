<?php

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

    $url = 'http://export.highcharts.com/';

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
        $value = $row->$yField;
        
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
                        'color'=> 'white',
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
    
    $url = 'http://export.highcharts.com/';
    
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
    
    //  return 'ok';
    
}

function toPieChart( $title, $yTitle, $serieTitle, $rows, $xField, $yField, $colors)
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
                    'distance'=> -50,
                    'color'=> 'white',
                    'style'=> (object)
                     [
                         'fontSize' => 15,
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
    
    $url = 'http://export.highcharts.com/';
    
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
  
 

  $url = 'http://export.highcharts.com/';

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
