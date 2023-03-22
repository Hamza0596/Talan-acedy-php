<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 23/04/2019
 * Time: 17:22
 */

namespace App\Service;


use Symfony\Component\Routing\RouterInterface;

class BreadcrumbsService
{
    const NAME_CURSUS = 'nameCursus';
    const NAME_MODULE = 'nameModule';
    const NAME_JOUR = 'nameJour';
    const NAME_SESSION = 'nameSession';
    const LABEL_CURSUS = 'Cursus';
    const LABEL_SESSIONS = 'Sessions';
    const LABEL_MODULES = 'Modules';
    const LABEL_JOURS = 'Jours';
    const ARRAY_NAME = ['nameCursus', 'nameModule', 'nameJour'];

    private $router;
    private $breadcrumbs = [];

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function addTitle($title) {
        $this->breadcrumbs['title-_*1'] = $title;
    }

    public function addBreadcrumbs($label,$route=null,$params = [] ) {
        $breadCount = count($this->breadcrumbs);
        if ($breadCount) {
            $breadCount += 1;
        }
        if ($route){
            $url = $this->router->generate($route, $params);
            $this->breadcrumbs[$label."-_*".$breadCount] = $url;
        }else {
            return $this->breadcrumbs[$label."-_*".$breadCount] = null;

        }
    }

    public function get() {
        return $this->breadcrumbs;
    }
}