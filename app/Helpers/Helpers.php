<?php

if (!function_exists('version_assets')) {
  function version_assets()
  {
    return config('app.version');
  }
}
