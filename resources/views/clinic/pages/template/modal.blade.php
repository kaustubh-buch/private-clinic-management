<div class="custom-modal has-footer tampate-popup" data-target="add-new-tampate" id="templateModal" tabindex="-1"
    aria-labelledby="templateModalLabel" data-lock="true">
    <div class="modal-backdrop"></div>
    <div class="modal-content-wrapper">
        <div class="modal-dialog">
            <div class="modal-inner-content">
                <div class="title-wrapper">
                    <h3 class="mr-5" id="templateModalTitle"></h3>
                    <a href="javascript:void(0);" class="close-btn modal-cancel"><em><img
                                src="{{ asset('front/images/popup-close-icon.svg') }}" alt="close-icon"></em></a>
                </div>
                <form id="templateForm" method="POST" id="formMethod">
                    @csrf
                    <div class="form-wrapper">
                        <div class="form-group">
                            <label for="name">{{ __('messages.labels.template_name') }}</label>
                            <input type="text" id="name" name="name"
                                placeholder="{{ __('messages.placeholders.template_name') }}"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" autocomplete="off">

                        </div>
                        <input type="hidden" name="_method" id="formMethod">
                        <input type="hidden" name="message" id="hiddenMessage">
                        <input type="hidden" name="campaign_type_id" id="campaign_type_id">
                        <input type="hidden" name="template_category_id" id="template_category_id">
                        <input type="hidden" name="category_name" id="category_name">
                        <input type="hidden" name="id" id="template_id">

                        <div class="form-group">
                            <label for="message">{{ __('messages.labels.template_message') }}</label>
                            <div class="message-wrapper">
                                <div id="message"
                                    class="message-block @error('message') is-invalid @enderror" data-placeholder="{{ __('messages.placeholders.template_message') }}">
                                    {!! old('message', '') !!}
                                </div>
                                <div class="btn-block d-flex gap-8 items-center justify-end">
                                    <button id="emoji-btn" type="button" class="emoji-btn"><em><img
                                                src="{{ asset('front/images/emoji-icon.svg') }}"
                                                alt="emoji-icon"></em></button>
                                    <a href="javascript:void(0);" class="primary-btn light-purple-btn w-auto small-btn"
                                        title="Opt-out" id="optOutBtn">{{ __('messages.labels.opt_out_message') }}</a>
                                    <div class="has-menu">
                                        <a href="javascript:void(0);" class="primary-btn light-purple-btn w-auto small-btn"
                                            title="Custom Fields">{{ __('messages.labels.custom_fields') }}<em
                                                class="arrow"><img
                                                    src="{{ asset('front/images/purple-arrow-icon.svg') }}"
                                                    alt="arrow-icon"></em></a>
                                        <div class="menu-wrapper" style="top: {{ $clinic->is_online_booking ? '-24.5rem' : '-21.1rem' }};">
                                            <ul>
                                                <li><a href="javascript:void(0);">{{ __('messages.labels.first_name') }}</a></li>
                                                <li><a href="javascript:void(0);">{{ __('messages.labels.last_name') }}</a></li>
                                                <li><a href="javascript:void(0);">{{ __('messages.labels.clinic_name') }}</a></li>
                                                @if($clinic->is_online_booking)
                                                    <li><a href="javascript:void(0);">{{ __('messages.labels.online_booking_link') }}</a>
                                                    </li>
                                                @endif
                                                <li><a
                                                        href="javascript:void(0);">{{ __('messages.labels.clinic_phone_number') }}</a>
                                                </li>
                                                <li><a href="javascript:void(0);">{{ __('messages.labels.last_recall_date') }}</a>
                                                    <div class="info-tooltip">
                                                        <div class="tooltip-icon"><img
                                                                src="{{ asset('front/images/grey-info-icon.svg') }}"
                                                                alt="info-icon"></div>
                                                        <div class="tooltip-text">This field automatically formats dates
                                                            into a natural, readable style: <p>• “February” (if the
                                                                recall was this year)</p>
                                                            <p>• “February last year” (if it was last year)</p>
                                                            <p>• “February 2023” (or the actual year, if more than two
                                                                years ago)</p>
                                                            <div class="tooltip-arrow" data-arrow></div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li><a href="javascript:void(0);">{{ __('messages.labels.insurance') }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="message-limit d-flex justify-end">
                                <p>{{ __('messages.labels.characters') }}: <span></span></p>
                                <p>{{ __('messages.labels.sms_segments') }}: <span></span></p>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer-block">
                        <div class="button-wrapper has-error-message">
                            <div class="error-message">
                                <div class="opt-out-message-text" style="display: none">
                                <p><em><img src="{{ asset('front/images/red-i-icon.svg') }}" alt="info-icon">
                                        {{ __('messages.page_texts.opt_out_message_text') }}
                                    </em>
                                </p>
                                </div>
                            </div>
                            <button type="submit" class="primary-btn small-btn w-auto" title="Save">Save</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('clinic.modals.emoji_picker')
