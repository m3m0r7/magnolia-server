<?php
namespace Magnolia\Traits;

use Magnolia\Traits\Behaviors\Session;

trait SessionUsable
{
    protected $session = null;

    public function getSession(): Session
    {
        if ($this->session !== null) {
            return $this->session;
        }

        return $this->session = new Session($this);
    }
}
