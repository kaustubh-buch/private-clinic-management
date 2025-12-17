@if ($column)
    @switch($column)
        @case('move')
            <em class="group-change-link move-insurance" data-id="{{ $row->id }}" data-type="{{ $preferredOnly ? 'preffered' : 'other' }}">
                <img src="{{ asset('front/images/group-'.($preferredOnly ? 'down' : 'up').'.svg') }}" alt="Move">
            </em>
            @break
        @case('action')
            <div class="threedot-menu-wrapper secondary">
                <div class="threedot-menu-link">
                    <img src="{{ asset('front/images/vertical-three-dots.svg') }}" alt="three-dots">
                </div>
                <div class="threedot-menu">
                    <ul>
                        <li>
                            <a href="#" title="{{ __('messages.page_texts.edit') }}" class="edit-insurance-link" data-id="{{ $row->id }}">{{ __('messages.page_texts.edit') }}</a>
                        </li>
                        <li>
                            <a href="#" title="{{ __('messages.page_texts.delete') }}" class="red-text delete-insurance-link" data-id="{{ $row->id }}">{{ __('messages.page_texts.delete') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            @break

        @default

    @endswitch
@endif
