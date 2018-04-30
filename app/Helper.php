<?php

if( ! function_exists('experiment') ){
  function experiment($name, $data = [])
  {
    return app()->make(\Calhoun\AB\ABTester::class)->experiment($name, $data)->run();
  }
}


if( ! function_exists('track') ){
  function track($referer, $pathInfo)
  {
    app()->make(\Calhoun\AB\ABTester::class)->track($referer, $pathInfo);
  }
}

if( ! function_exists('report') ){
  function report($nomenclature)
  {
    return app()->make(\Calhoun\AB\ABTester::class)->report($nomenclature);
  }
}

?>
