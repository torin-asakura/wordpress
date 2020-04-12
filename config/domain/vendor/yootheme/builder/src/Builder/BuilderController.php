<?php

namespace YOOtheme\Builder;

use YOOtheme\Builder;
use YOOtheme\Http\Request;
use YOOtheme\Http\Response;
use YOOtheme\Storage;

class BuilderController
{
    public function encodeLayout(Request $request, Response $response, Builder $builder)
    {
        return $response->withJson($builder->load(json_encode($request('layout'))));
    }

    public function addElement(Request $request, Response $response, Storage $storage)
    {
        $storage->set("library.{$request('id')}", $request('element'));

        return $response->withJson(['message' => 'success']);
    }

    public function removeElement(Request $request, Response $response, Storage $storage)
    {
        $storage->del("library.{$request('id')}");

        return $response->withJson(['message' => 'success']);
    }
}
