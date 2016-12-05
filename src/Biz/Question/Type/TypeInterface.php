<?php

interface TypeInterface
{
    public function create();

    public function update();

    public function delete();

    public function get();

    public function filter();
}