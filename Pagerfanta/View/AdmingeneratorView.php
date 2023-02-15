<?php

namespace Admingenerator\GeneratorBundle\Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\ViewInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdmingeneratorView implements ViewInterface
{
    public function __construct(protected readonly TranslatorInterface $translator)
    {
    }

    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = array()): string
    {
        $options = array_merge([
            'proximity'              => 2,
            'previous_message'       => $this->translator->trans('pagerfanta.previous', [], 'Admingenerator'),
            'next_message'           => $this->translator->trans('pagerfanta.next', [], 'Admingenerator'),
            'css_disabled_class'     => 'disabled',
            'css_dots_class'         => 'dots',
            'css_current_class'      => 'active',
            'css_alignment_class'    => 'pagination-right',
            'css_buttons_size_class' => 'pagination-sm',
            'css_custom_class'       => ''
        ], $options);

        $currentPage = $pagerfanta->getCurrentPage();

        $startPage = $currentPage - $options['proximity'];
        $endPage = $currentPage + $options['proximity'];

        if ($startPage < 1) {
            $endPage = min($endPage + (1 - $startPage), $pagerfanta->getNbPages());
            $startPage = 1;
        }
        if ($endPage > $pagerfanta->getNbPages()) {
            $startPage = max($startPage - ($endPage - $pagerfanta->getNbPages()), 1);
            $endPage = $pagerfanta->getNbPages();
        }

        $pages = [];

        // previous
        if ($pagerfanta->hasPreviousPage()) {
            $pages[] = [$pagerfanta->getPreviousPage(), $options['previous_message']];
        }

        // first
        if ($startPage > 1) {
            $pages[] = [1, 1];
            if (3 == $startPage) {
                $pages[] = [2, 2];
            } elseif (2 != $startPage) {
                $pages[] = sprintf(
                    '<li class="%s"><span class="%s">...</span></li>',
                    $options['css_disabled_class'],
                    $options['css_dots_class']
                );
            }
        }

        // pages
        for ($page = $startPage; $page <= $endPage; $page++) {
            if ($page == $currentPage) {
                $pages[] = sprintf(
                    '<li class="%s"><a href="#" class="number">%s</a></li>',
                    $options['css_current_class'],
                    $page
                );
            } else {
                $pages[] = [$page, $page];
            }
        }

        // last
        if ($pagerfanta->getNbPages() > $endPage) {
            if ($pagerfanta->getNbPages() > ($endPage + 1)) {
                if ($pagerfanta->getNbPages() > ($endPage + 2)) {
                    $pages[] = sprintf(
                        '<li class="%s"><span class="%s">...</span></li>',
                        $options['css_disabled_class'],
                        $options['css_dots_class']
                    );
                } else {
                    $pages[] = [$endPage + 1, $endPage + 1];
                }
            }

            $pages[] = [$pagerfanta->getNbPages(), $pagerfanta->getNbPages()];
        }

        // next
        if ($pagerfanta->hasNextPage()) {
            $pages[] = [$pagerfanta->getNextPage(), $options['next_message']];
        }

        // process
        $pagesHtml = '';
        foreach ($pages as $page) {
            if (is_string($page)) {
                $pagesHtml .= $page;
            } else {
                if (is_string($page[1])) {
                    $pagesHtml .= '<li><a href="'.$routeGenerator($page[0]).'">'.$page[1].'</a></li>';
                } else {
                    $pagesHtml .= '<li><a href="'.$routeGenerator($page[0]).'" class="number">'.$page[1].'</a></li>';
                }
            }
        }

        return sprintf(
            '<ul class="pagination %s %s">%s</ul>',
            $options['css_buttons_size_class'],
            $options['css_custom_class'],
            $pagesHtml
        );
    }

    public function getName(): string
    {
        return 'admingenerator';
    }
}
