<?php
use Symfony\Component\HttpFoundation\Response;

$conversion = $app['controllers_factory'];

$conversion->get('/api/get/navigation/{directory}', 
    function ($directory) use ($app) {
        try {
            $dir = str_replace("|", "/", $directory);
            $data = $app['service.utils']->navigationSystem($dir);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

$conversion->get('/api/get/files/to/convert', 
    function () use ($app) {
        try {
            $data = $app['service.conversion']->getFilesToConvert($app['parameter.path.in.convert']);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

$conversion->get('/api/convert/all', 
    function () use ($app) {
        try {
            $data = $app['service.conversion']->convertAll($app['parameter.path.in.convert'], 
                $app['parameter.path.end.convert']);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

$conversion->get('/api/convert/file/{file}', 
    function ($file) use ($app) {
        try {
            $file = str_replace("|", "/", $file);
            $data = $app['service.conversion']->convertFile($file);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

return $conversion;