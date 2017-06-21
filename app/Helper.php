<?php

function experiment($name, $data = [])
{
  return app()->make(\Calhoun\AB\ABTester::class)->experiment($name, $data)->run();
}

function track($referer, $pathInfo)
{
  app()->make(\Calhoun\AB\ABTester::class)->track($referer, $pathInfo);
}

function report($nomenclature)
{
  return app()->make(\Calhoun\AB\ABTester::class)->report($nomenclature);
}

?>
