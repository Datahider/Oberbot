<?php

namespace losthost\Oberbot\builders;
use DateTimeImmutable;
use DateInterval;

/**
 * Абстрактный класс получения отчета по записям timer_event
 *
 * @author drweb_000
 */
abstract class AbstractBuilder {
    
    const TIME_FORMAT = '%r%A:%I';
    
    abstract public function build(?array $params=null) : array;
    
    protected function checkBuildParams(?array &$params) {
        if (is_null($params)) {
            $params = [];
        } elseif (is_scalar($params)) {
            $params = [$params];
        }
    }
    
}
