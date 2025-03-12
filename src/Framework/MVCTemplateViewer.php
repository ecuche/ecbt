<?php
declare(strict_types=1);
namespace Framework;
class MVCTemplateViewer implements TemplateViewerInterface
{
    public function render(string $template, array|object $data = []): string
    {
        $views_dir = dirname(__DIR__, 2) ."/"."views/";
        $template =  lcfirst($template);
        $code = file_get_contents("{$views_dir}{$template}.cav");
        do{
            if(preg_match('#{% extends "(?<template>.*)" %}#', $code, $matches) === 1){
                $base = file_get_contents("{$views_dir}{$matches['template']}.cav");

                do{
                    $base = $this->loadIncludes($views_dir, $base);
                }while(preg_match('#{% include "(?<template>.*?)" #', $base, $matches) === 1);


                $blocks = $this->getBlocks($code);
                $code = $this->replaceYields($base, $blocks);  
            }
        }while(preg_match('#{% extends "(?<template>.*)" %}#', $code, $matches) === 1);    

        $code = $this->loadIncludes($views_dir, $code);
        $code = $this->replaceENV($code);
        $code = $this->capWords($code);
        $code = $this->replacePHP($code);
        $code = $this->Variables($code);
        $code = $this->cleanVariables($code);

        extract($data, EXTR_SKIP);
        ob_start();
        eval("?>$code");
        return ob_get_clean();
    }

    private function cleanVariables(string $code) : string
    {
        return preg_replace("#{{\s*(\S+)\s*}}#", "<?= htmlspecialchars($1 ?? '') ?>", $code);
    }
    private function Variables(string $code) : string
    {
        return preg_replace("#{!\s*(\S+)\s*!}#", "<?= ($1 ?? '') ?>", $code);
    }

    private function replacePHP(string $code) : string
    {
        return preg_replace("#{%\s*(.+)\s*%}#", "<?php $1 ?>", $code);
    }

    private function replaceENV(string $code) : string
    {
        return  preg_replace('#{<\s*(\S+)\s*>}#', '<?= $_ENV["$1"] ?>', $code);
    }

    private function capWords(string $code) : string
    {
        return  preg_replace("#{=\s*(\S+)\s*=}#", "<?= ucwords(htmlspecialchars($1 ?? '')) ?>", $code);
    }

    private function getBlocks(string $code) : array|object
    {
        preg_match_all("#{% block (?<name>\w+) %}(?<content>.*?){% endblock %}#s", $code, $matches, PREG_SET_ORDER);
        $blocks = [];
        foreach ($matches as $match) {
            $blocks[$match['name']] = $match['content'];
        }
        return $blocks;
    }

    private function replaceYields(string $code, array $blocks) : string
    {
        preg_match_all("#{% yield (?<name>\w+) %}#", $code, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $name = $match["name"];
            $block = $blocks[$name];
            if(!empty($block)) {
                $code = preg_replace("#{% yield $name %}#", $block, $code);
            }
        }
        return $code;
    }

    private function loadIncludes(string $dir, string $code) : string
    {
        preg_match_all('#{% include "(?<template>.*?)" #', $code, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $template = $match['template'];
            $contents = file_get_contents("{$dir}{$template}.cav");

            $code = preg_replace("#{% include \"$template\" %}#", $contents, $code);
        }
        return $code;
    }
}