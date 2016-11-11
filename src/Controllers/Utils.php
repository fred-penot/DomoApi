<?php
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

$utils = $app['controllers_factory'];

$utils->post('/api/japscan/generate', 
    function (Request $request) use ($app) {
        try {
            $data = $app['service.japscan']->generate($request->get('url'), 
                $request->get('tomeMin'), $request->get('tomeMax'), $request->get('pageMin'), 
                $request->get('pageMax'));
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/test/manga/{token}', 
    function ($token) use ($app) {
        try {
            $url = 'http://cdn.japscan.com/cr-images/Dragon%20Ball%20Multiverse/__TOMENUM__/__PAGENUM__.jpg';
            $data = $app['service.japscan']->generate($url, 43, 51, 961, 1160);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/grab/manga/title/{token}', 
    function ($token) use ($app) {
        try {
            $mangas = $app['service.japscan']->grabMangaTitle();
            if ($mangas instanceof \Exception) {
                throw new \Exception($mangas->getMessage());
            }
            $app['retour'] = $mangas;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/grab/manga/tome/{token}', 
    function ($token) use ($app) {
        try {
            $lastMangaInsert = $app['service.japscan']->grabTomeMangas();
            if ($lastMangaInsert instanceof \Exception) {
                throw new \Exception($lastMangaInsert->getMessage());
            }
            $app['retour'] = $lastMangaInsert;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/grab/manga/chapter/{token}', 
    function ($token) use ($app) {
        try {
            $lastMangaInsert = $app['service.japscan']->grabEbookMangas();
            if ($lastMangaInsert instanceof \Exception) {
                throw new \Exception($lastMangaInsert->getMessage());
            }
            $app['retour'] = $lastMangaInsert;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/manga/title/{token}/{search}', 
    function ($token, $search) use ($app) {
        try {
            $mangas = array();
            if (strlen($search) > 1) {
                $mangas = $app['service.japscan']->getMangasBySearch($search);
                if ($mangas instanceof \Exception) {
                    throw new \Exception($mangas->getMessage());
                }
            }
            $app['retour'] = array(
                "mangas" => $mangas
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/manga/title/begin/by/{token}/{search}', 
    function ($token, $search) use ($app) {
        try {
            $mangas = array();
            if (strlen($search) > 0) {
                $mangas = $app['service.japscan']->getMangasBeginBy($search);
                if ($mangas instanceof \Exception) {
                    throw new \Exception($mangas->getMessage());
                }
            }
            $app['retour'] = array(
                "mangas" => $mangas
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/manga/search/like/{token}/{like}', 
    function ($token, $like) use ($app) {
        try {
            $mangas = array();
            $mangas = $app['service.japscan']->getMangasLike($like);
            if ($mangas instanceof \Exception) {
                throw new \Exception($mangas->getMessage());
            }
            $app['retour'] = array(
                "mangas" => $mangas
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/manga/info/{token}/{id}', 
    function ($token, $id) use ($app) {
        try {
            $manga = $app['service.japscan']->getMangaById($id);
            if ($mangas instanceof \Exception) {
                throw new \Exception($mangas->getMessage());
            }
            $app['retour'] = array(
                "info" => $manga
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/manga/tome/list/{token}/{mangaId}', 
    function ($token, $mangaId) use ($app) {
        try {
            $tomes = $app['service.japscan']->getMangaTomeList($mangaId);
            if ($tomes instanceof \Exception) {
                throw new \Exception($tomes->getMessage());
            }
            $app['retour'] = array(
                "tomes" => $tomes
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/manga/chapter/{token}/{mangaId}', 
    function ($token, $mangaId) use ($app) {
        try {
            $chapters = $app['service.japscan']->getMangaChapterListByManga($mangaId);
            if ($chapters instanceof \Exception) {
                throw new \Exception($chapters->getMessage());
            }
            $app['retour'] = array(
                "chapters" => $chapters
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/manga/chapter/tome/{token}/{chapterId}', 
    function ($token, $chapterId) use ($app) {
        try {
            $tomes = $app['service.japscan']->getMangaTomeByChapter($chapterId);
            if ($tomes instanceof \Exception) {
                throw new \Exception($tomes->getMessage());
            }
            $app['retour'] = array(
                "tomes" => $tomes
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/manga/tome/chapter/{token}/{tomeId}', 
    function ($token, $tomeId) use ($app) {
        try {
            $chapters = $app['service.japscan']->getMangaChapterListByTome($tomeId);
            if ($chapters instanceof \Exception) {
                throw new \Exception($chapters->getMessage());
            }
            $app['retour'] = array(
                "chapters" => $chapters
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/check/archive/manga/{token}/{tomeId}/{chapterId}', 
    function ($token, $tomeId, $chapterId) use ($app) {
        try {
            $check = $app['service.japscan']->checkArchiveManga($tomeId, $chapterId);
            if ($check instanceof \Exception) {
                throw new \Exception($check->getMessage());
            }
            $app['retour'] = array(
                "check" => $check
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/generate/manga/{token}/{tomeId}/{chapterId}', 
    function ($token, $tomeId, $chapterId) use ($app) {
        try {
            $downloadId = $app['service.japscan']->insertMangaDownload($tomeId, $chapterId, 
                $app['user_id']);
            if ($downloadId instanceof \Exception) {
                throw new \Exception($downloadId->getMessage());
            }
            $checkDownload = $app['service.japscan']->checkDownload($app['user_id'], $downloadId);
            if ($checkDownload instanceof \Exception) {
                throw new \Exception($checkDownload->getMessage());
            }
            $generate = "ok";
            if (! $checkDownload) {
                $generate = $app['service.japscan']->generateManga($app['user_id'], $tomeId, 
                    $chapterId, $downloadId);
                if ($generate instanceof \Exception) {
                    throw new \Exception($generate->getMessage());
                }
            }
            
            $app['retour'] = array(
                "time" => $generate
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/check/tome/manga/{token}/{title}/{nbTome}', 
    function ($token, $title, $nbTome) use ($app) {
        try {
            $tome = $app['service.japscan']->getTomeByTitle($title, $nbTome);
            if ($tome instanceof \Exception) {
                throw new \Exception($tome->getMessage());
            }
            $app['retour'] = array(
                "tome" => $tome
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/generate/tome/manga/{token}/{title}/{nbTome}', 
    function ($token, $title, $nbTome) use ($app) {
        try {
            $tome = $app['service.japscan']->getTomeByTitle($title, $nbTome);
            if ($tome instanceof \Exception) {
                throw new \Exception($tome->getMessage());
            }
            $downloadId = $app['service.japscan']->insertMangaDownload($tome['id'], 0, 
                $app['user_id']);
            if ($downloadId instanceof \Exception) {
                throw new \Exception($downloadId->getMessage());
            }
            $checkDownload = $app['service.japscan']->checkDownload($app['user_id'], $downloadId);
            if ($checkDownload instanceof \Exception) {
                throw new \Exception($checkDownload->getMessage());
            }
            $generate = "ok";
            if (! $checkDownload) {
                $generate = $app['service.japscan']->generateManga($app['user_id'], $tome['id'], 0, 
                    $downloadId);
                if ($generate instanceof \Exception) {
                    throw new \Exception($generate->getMessage());
                }
            }
            
            $app['retour'] = array(
                "time" => $generate
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/info/download/manga/{token}/{tomeId}/{chapterId}', 
    function ($token, $tomeId, $chapterId) use ($app) {
        try {
            $info = $app['service.japscan']->getLastMangaDownloadInfo($tomeId, $chapterId);
            if ($info instanceof \Exception) {
                throw new \Exception($info->getMessage());
            }
            $app['retour'] = array(
                "info" => $info
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/current/download/manga/{token}', 
    function ($token) use ($app) {
        try {
            $download = $app['service.japscan']->getCurrrentMangaDownloads($app['user_id']);
            if ($download instanceof \Exception) {
                throw new \Exception($download->getMessage());
            }
            $app['retour'] = array(
                "download" => $download
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/get/history/download/manga/{token}', 
    function ($token) use ($app) {
        try {
            $history = $app['service.japscan']->getHistoryMangaDownload($app['user_id']);
            if ($history instanceof \Exception) {
                throw new \Exception($history->getMessage());
            }
            $app['retour'] = array(
                "history" => $history
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/delete/history/download/manga/{token}/{historyId}', 
    function ($token, $historyId) use ($app) {
        try {
            $app['service.japscan']->deleteHistoryMangaDownload($historyId);
            $history = $app['service.japscan']->getHistoryMangaDownload($app['user_id']);
            if ($history instanceof \Exception) {
                throw new \Exception($history->getMessage());
            }
            $app['retour'] = array(
                "history" => $history
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$utils->get('/api/japscan/link/download/manga/{token}/{tomeId}/{chapterId}', 
    function ($token, $tomeId, $chapterId) use ($app) {
        try {
            $link = $app['service.japscan']->getLinkMangaDownload($tomeId, $chapterId);
            if ($link instanceof \Exception) {
                throw new \Exception($info->getMessage());
            }
            /*
             * $app['retour'] = array(
             * "link" => $link
             * );
             */
        } catch (\Exception $ex) {
            // $app['retour'] = $ex;
        }
        /*
         * $header = array(
         * "Access-Control-Allow-Origin" => "*"
         * );
         * $app['monolog']->addInfo($link);
         * $response = new BinaryFileResponse($link, 200, $header);
         * $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
         *
         * return $response;
         */
        
        header("Content-Type: application/force-download; name=\"" . basename($link) . "\"");
        header("Content-Transfer-Encoding: application/pdf");
        header("Content-Length: " . filesize($link));
        header("Content-Disposition: attachment; filename=\"" . basename($link) . "\"");
        
        /*
         * rewind($handle);
         * fpassthru($handle);
         * fclose($handle);
         */
        
        readfile($link);
        
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT);

return $utils;