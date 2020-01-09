<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2020 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Action\Admin\Talk;

use OpenCFP\Domain\Services;
use OpenCFP\Domain\Talk;
use OpenCFP\Http\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation;

final class IndexAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var View\TalkHelper
     */
    private $talkHelper;

    /**
     * @var Talk\TalkFilter
     */
    private $talkFilter;

    public function __construct(Services\Authentication $authentication, View\TalkHelper $talkHelper, Talk\TalkFilter $talkFilter)
    {
        $this->authentication = $authentication;
        $this->talkHelper     = $talkHelper;
        $this->talkFilter     = $talkFilter;
    }

    /**
     * @Template("admin/talks/index.twig")
     *
     * @param HttpFoundation\Request $request
     *
     * @throws Services\NotAuthenticatedException
     *
     * @return array
     */
    public function __invoke(HttpFoundation\Request $request): array
    {
        $adminUserId = $this->authentication->user()->getId();

        $options = [
            'order_by' => $request->query->get('order_by'),
            'sort'     => $request->query->get('sort'),
        ];

        $formattedTalks = $this->talkFilter->getTalks(
            $adminUserId,
            $request->query->get('filter'),
            $request->query->get('category'),
            $request->query->get('type'),
            $options
        );

        $perPage = (int) $request->query->get('per_page') ?: 20;

        $pagination = new Services\Pagination(
            $formattedTalks,
            $perPage
        );

        $pagination->setCurrentPage($request->query->get('page'));

        return [
            'pagination' => $pagination->createView(
                '/admin/talks?',
                $request->query->all()
            ),
            'talks'          => $pagination->getFanta(),
            'page'           => $pagination->getCurrentPage(),
            'current_page'   => $request->getRequestUri(),
            'totalRecords'   => \count($formattedTalks),
            'filter'         => $request->query->get('filter'),
            'category'       => $request->query->get('category'),
            'type'           => $request->query->get('type'),
            'talkCategories' => $this->talkHelper->getTalkCategories(),
            'talkTypes'      => $this->talkHelper->getTalkTypes(),
            'per_page'       => $perPage,
            'sort'           => $request->query->get('sort'),
            'order_by'       => $request->query->get('order_by'),
        ];
    }
}
