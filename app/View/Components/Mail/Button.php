<?php

namespace App\View\Components\Mail;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public string $url;

    public string $title;

    /**
     * Create a new component instance.
     */
    public function __construct(string $url = '', string $title = '')
    {
        $this->url = $url;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.mail.button');
    }
}
