<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CsvService
{
    public function export($data, $filename, $context = [])
    {
        $context = \array_merge([
            CsvEncoder::DELIMITER_KEY => ';'
        ], $context);

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $response = new Response($serializer->encode($data, CsvEncoder::FORMAT, $context));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }

    public function import($filename, $options = [])
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        return $serializer->decode(file_get_contents($filename), CsvEncoder::FORMAT, $options);
    }
}
