<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Provider\Gateways;

use HTMLPurifier;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class RequestCleaner
{
    /**
     * @var HTMLPurifier
     */
    private $purifier;

    /**
     * @param HTMLPurifier $purifier
     */
    public function __construct(HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public function __invoke(Request $request, Application $app)
    {
        $request->query->replace($this->clean($request->query->all()));
        $request->request->replace($this->clean($request->request->all()));
    }

    /**
     * @param array $data
     */
    private function clean(array $data)
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                $sanitized[$key] = $this->clean($value);
            } else {
                $sanitized[$key] = \preg_replace(
                    ['/&amp;/', '/&lt;\b/', '/\b&gt;/'],
                    ['&', '<', '>'],
                    $this->purifier->purify($value)
                );
            }
        }

        return $sanitized;
    }
}
