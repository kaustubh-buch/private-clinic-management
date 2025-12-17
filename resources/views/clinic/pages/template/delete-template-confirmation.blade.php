<div class="custom-modal center-modal small-modal" data-target="delete-modal" id="delete-modal">	
    <div class="modal-backdrop"></div>
    <div class="modal-content-wrapper">
        <div class="modal-dialog">
            <div class="modal-inner-content">
                <div class="close-btn-wrapper">                       
                    <a href="#" class="close-btn modal-cancel"><em><img src="{{ asset('front/images/popup-close-icon.svg') }}" alt="close-icon"></em></a>
                </div>
               <em class="tick-image-wrapper purple-bg"><img src="{{ asset('front/images/purple-delete-icon.svg')}}" alt="delete-icon" /></em>    
               <h3 id="template-name-placeholder"></h3>                                                      
               <div class="btn-block d-flex gap-8 justify-center mt-2">
                  <a href="#" class="outline-btn w-auto small-btn modal-cancel" title="Cancel">Cancel</a>                   
                  <a href="#" class="primary-btn w-auto small-btn" title="Delete">Delete</a>                               
               </div>         
            </div>
        </div>
    </div>
</div>