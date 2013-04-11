<?php

namespace Herrera\Wise;

/**
 * Indicates that the class supports the use of a Wise instance.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface WiseAwareInterface
{
    /**
     * Returns the Wise instance.
     *
     * @return Wise The instance.
     */
    public function getWise();

    /**
     * Sets a Wise instance.
     *
     * @param Wise $wise An instance.
     */
    public function setWise(Wise $wise);
}
