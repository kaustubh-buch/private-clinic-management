// Add top spacing to main wrapper
function adjustPadding() {
  var headerHeight = jQuery('.site-header').outerHeight();
  jQuery('.page-wrapper').css('padding-top', headerHeight);
  jQuery('.sidebar-outer-wrapper').css('top', headerHeight);  
}

// function if we only have header-bottom in page
function adjustPagePadding() {
  var headerHeight = jQuery('.header-bottom').outerHeight();
  jQuery('.page-wrapper').css('padding-top', headerHeight);
  jQuery('.sidebar-outer-wrapper').css('top', headerHeight);
}

// Counting white card height
function fullHeightWhiteCard() {
  if (jQuery('.full-height-white-card').length) {
    var whiteCardOffSet = jQuery('.full-height-white-card').offset().top;
    var windowHeight = jQuery(window).height() - 22;
    var fullHeightCard = windowHeight - whiteCardOffSet;
    jQuery('.full-height-white-card').css('height', fullHeightCard + 'px');
  }
}

// Counting white card min height
function fullMinHeightWhiteCard() {
  if (jQuery('.full-min-height-white-card').length) {
    var whiteCardOffSet = jQuery('.full-min-height-white-card').offset().top;
    var windowHeight = jQuery(window).height() - 22;
    var fullHeightCard = windowHeight - whiteCardOffSet;
    jQuery('.full-min-height-white-card').css('min-height', fullHeightCard + 'px');
  }
}

// Select with image option
function formatOption(option) {
  if (!option.id) return option.text;

  const image = jQuery(option.element).data('image');
  const text = option.text;

  if (image) {
    return jQuery(
      `<span><img src="${image}" class="option-icon" style="width: 16px; height: 16px;position: relative; top: -2px; margin-right: 5px;" />${text}</span>`
    );
  } else {
    return option.text;
  }
}

// main title js
mainTitleFlag = true;
function moveMainTitle() {
  if (jQuery(window).width() <= 1199) {
    if (mainTitleFlag) {
      jQuery('.site-header .bottom-right-block h1').detach().prependTo(".main-content .page-inner-wrapper");
      mainTitleFlag = false;
    }
  }
  else {
    if (!mainTitleFlag) {
      jQuery(".main-content .page-inner-wrapper h1").detach().insertBefore(".site-header .bottom-right-block .button-wrapper")
      mainTitleFlag = true;
    }
  }
}

function stepperInit() {
  jQuery('.stepper-outer-wrapper').each(function () {
    const $stepper = jQuery(this);
    // Once it shows the first step, fade in
    $stepper.on('showStep', function () {
      $stepper.css('opacity', '1');
    });
  });
}

function onDataTableInit() {
  jQuery('.custom-table-wrapper table').each(function () {
    const $table = jQuery(this);
    // If DataTable is already initialized, trigger init manually
    if ($.fn.dataTable.isDataTable($table)) {
      $table.css('opacity', '1');
    } else {
      // Otherwise, listen for init event
      $table.one('init.dt', function () {
        $table.css('opacity', '1');
      });
    }
    if ($table.find('tbody tr').length <= 2) {
      $table.addClass('has-only-two-row');
       jQuery(document).on('click', '.threedot-menu-wrapper .threedot-menu-link', function (e) {       
      });
    }
  });
}

// datatable
function initializeCustomTables() {
  jQuery('.custom-data-table').each(function () {
    const $table = jQuery(this);
    if (!$.fn.DataTable.isDataTable($table[0])) {
      const noteText = $table.data('note');

      $table.on('init.dt', function () {
        // Append note if applicable
        const noteText = $table.data('note');
        if (noteText) {
          const $wrapper = $table.closest('.dt-layout-table');
          if (!$wrapper.next('.indicates-note').length) {
            $wrapper.after(`<div class="indicates-note">${noteText}</div>`);
          }
        }
      });

      // Initialize DataTable
      $table.DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: false,
        searching: true,
        info: true,
        ordering: false,
        responsive: true,
        language: {
          info: "Showing _START_ of _TOTAL_ Results",
          paginate: {
            previous: '<img src="/front/images/prev-arrow.svg">',
            next: '<img src="/front/images/next-arrow.svg">'
          }
        },
        layout: {
          bottomEnd: {
            paging: {
              firstLast: false
            }
          }
        }
      });
    }
  });
}
// Tooltip

jQuery(document).ready(function () {
  adjustPadding();
  setTimeout(function () {    
    fullHeightWhiteCard();
    fullMinHeightWhiteCard();
    onDataTableInit();
  }, 300)
  moveMainTitle();
  stepperInit();

  if (window.FloatingUIDOM) {
  const { computePosition, autoUpdate, offset, flip, shift, arrow } = window.FloatingUIDOM;
  const tooltipTriggers = document.querySelectorAll('.tooltip-icon');
  tooltipTriggers.forEach(button => {
    const tooltip = button.nextElementSibling;
    const tooltipArrow = tooltip.querySelector('.tooltip-arrow');    

    button.addEventListener('mouseenter', async () => {         
      tooltip.style.display = 'block';
      let isInSidebar = button.closest('.sidebar-outer-wrapper'); 
      // Get placement from HTML attribute or fallback to 'top-start'
      const placement = button.getAttribute('data-placement') || 'top-start';
      const { x, y, placement: resolvedPlacement, middlewareData } = await computePosition(button, tooltip, {
        placement,
        middleware: [
        offset(({ placement }) => {
          const mainAxis = 14;
          const crossAxis = -15;
          return { mainAxis, crossAxis };
        }),
        flip({
          fallbackPlacements: ['top', 'top-end', 'bottom', 'bottom-start', 'bottom-end', 'left-start'],
        }),
        shift({ padding: 8 }),
        arrow({ element: tooltipArrow }),
      ],
      strategy: 'absolute',
    });

      if (resolvedPlacement === 'top-end') {
      // Custom adjustment
        Object.assign(tooltip.style, {
          left: `${x + 30}px`, // Example tweak
          top: `${y}px`,
        });
      } else {
        // Default positioning
        Object.assign(tooltip.style, {
          left: `${x}px`,
          top: `${y}px`,
        });
        if (isInSidebar) tooltip.style.top = `${y + 13}px`;
      }


      // Use the resolved placement (after flip etc.)
      // Position the arrow
      tooltipArrow.style.left = '';
      tooltipArrow.style.top = '';
      tooltipArrow.style.right = '';
      tooltipArrow.style.bottom = '';
      const { x: arrowX, y: arrowY } = middlewareData.arrow;
      const [side, alignment] = resolvedPlacement.split('-');
      switch (side) {
        case 'top':
          tooltipArrow.style.bottom = '-6.5px';
          tooltipArrow.style.transform = 'rotate(45deg)';
          if (alignment === 'start') {
            tooltipArrow.style.left = `${arrowX}px`;
          } else if (alignment === 'end') {
            tooltipArrow.style.left = `${arrowX - 15}px`;
          } else {
            tooltipArrow.style.left = `${arrowX}px`;
          }
          break;
        case 'bottom':
          tooltipArrow.style.top = '-6.5px';
          tooltipArrow.style.left = `${arrowX}px`;
          tooltipArrow.style.transform = 'rotate(225deg)';
          break;
        case 'left':
          tooltipArrow.style.right = '-6.5px';
          tooltipArrow.style.top = `${arrowY}px`;
          tooltipArrow.style.transform = 'rotate(-45deg)';
          break;
        case 'right':
          tooltipArrow.style.left = '-6.5px';
          tooltipArrow.style.top = `${arrowY}px`;
          tooltipArrow.style.transform = 'rotate(135deg)';
          break;
      }
      });

      button.addEventListener('mouseleave', () => {
        tooltip.style.display = 'none';
      });     
    });

    const wrapperTriggers = document.querySelectorAll('.insurance-page-wrapper .tooltip-icon');
wrapperTriggers.forEach(button => {
  const tooltipWrapper = button.closest('.info-tooltip');
  const tooltip = button.nextElementSibling;
  const tooltipArrow = tooltip.querySelector('.tooltip-arrow');
    if (tooltipWrapper.classList.contains('small-tooltip')) {
      tooltip.classList.add('small-tooltip');
    }
  // Append tooltip to body once
  if (!tooltip.dataset.appendedToBody) {
    document.body.appendChild(tooltip);
    tooltip.dataset.appendedToBody = "true";
  }

  button.addEventListener('mouseenter', async () => {
    tooltip.style.display = 'block';

   
    const placement = button.getAttribute('data-placement') || 'top-start';

    const { x, y, placement: resolvedPlacement, middlewareData } = await computePosition(button, tooltip, {
      placement,
      middleware: [
        offset(8),
        flip(),
        shift({ padding: 8 }),
        arrow({ element: tooltipArrow }),
      ],
      strategy: 'absolute', // relative to body
    });

    // Position tooltip relative to body
    Object.assign(tooltip.style, {
      left: `${x - 15}px`,
      top: `${y - 10}px`,
      position: 'absolute',
      zIndex: 9999,
    });

    // Reset arrow
    tooltipArrow.style.left = '';
    tooltipArrow.style.top = '';
    tooltipArrow.style.right = '';
    tooltipArrow.style.bottom = '';

    const { x: arrowX, y: arrowY } = middlewareData.arrow || {};
    const [side] = resolvedPlacement.split('-');

    switch (side) {
      case 'top':
        tooltipArrow.style.bottom = '-6.5px';
        tooltipArrow.style.left = `${arrowX + 15}px`;
        tooltipArrow.style.transform = 'rotate(45deg)';
        break;
      case 'bottom':
        tooltipArrow.style.top = '-6.5px';
        tooltipArrow.style.left = `${arrowX || 0}px`;
        tooltipArrow.style.transform = 'rotate(225deg)';
        break;
      case 'left':
        tooltipArrow.style.right = '-6.5px';
        tooltipArrow.style.top = `${arrowY || 0}px`;
        tooltipArrow.style.transform = 'rotate(-45deg)';
        break;
      case 'right':
        tooltipArrow.style.left = '-6.5px';
        tooltipArrow.style.top = `${arrowY || 0}px`;
        tooltipArrow.style.transform = 'rotate(135deg)';
        break;
    }
  });

  button.addEventListener('mouseleave', () => {
    tooltip.style.display = 'none';
  });
});

}

  // insurance page tooltip

  // chatbox open close mobile
  jQuery('.inbox-outer .chatbox-sidebar .chatbox-list .chatbox-list-item').click(function (e) {
    if (jQuery(window).width() < 992) {
      jQuery(this).closest('.inbox-outer').addClass('open-chat-panel');
    }
  });
  jQuery('.inbox-outer .back-to-chats').click(function (e) {
    if (jQuery(window).width() < 992) {
      jQuery(this).closest('.inbox-outer').removeClass('open-chat-panel');
    }
  });

  jQuery('.close-top-header').click(function(){
      jQuery('.header-top').animate({
        transform: 'translateY(-100%)'
      }, 400, function () {
        adjustPagePadding();
        fullMinHeightWhiteCard();
        fullHeightWhiteCard();
        jQuery(this).hide();
      });
  });

  // OTP functionality
  jQuery('.otp-input').on('keyup', function (e) {
    var key = e.keyCode || e.charCode;
    var thisInput = $(this);
    var prevInput = thisInput.parent().prev('.otp-input-wrapper').find('.otp-input');
    var nextInput = thisInput.parent().next('.otp-input-wrapper').find('.otp-input');

    if (key === 8 || key === 46) {
      if (thisInput.val() === '') {
        prevInput.focus();
      }
    } else if (this.value.length === this.maxLength) {
      if (nextInput.length === 0) {
        jQuery('.continue-btn').focus();
      } else {
        nextInput.focus();
      }
    }
  });

  jQuery('.otp-input').on('input', function () {
    var val = jQuery(this).val();
    if (val.length > 1) {
      jQuery(this).val(val[0]);
    }
  });

  // custom select
  if (jQuery('.custom-select').length) {
    jQuery('.custom-select').each(function () {
      jQuery(this).select2({
        minimumResultsForSearch: -1,
        dropdownParent: jQuery(this).closest('.form-group'),
      })
    });
  }

  if (jQuery('.custom-select-with-image').length) {
    jQuery('.custom-select-with-image').select2({
      templateResult: formatOption,
      templateSelection: formatOption,
      placeholder: jQuery('.custom-select-with-image').data('placeholder'),
      minimumResultsForSearch: -1,
      dropdownParent: jQuery('.custom-select-with-image').closest('.form-group'),
    });
  }


  // stepper JS
  if (jQuery('#secure-account-stepper').length) {
    const $stepper = jQuery('#secure-account-stepper');

    $stepper.smartWizard({
      toolbar: {
        showNextButton: false,
        showPreviousButton: false,
      },
      autoAdjustHeight:true,
      enableURLhash: false,
    });

    let initialStep = Number($stepper.smartWizard("getStepIndex")) + 1;
     if (typeof initialStep === 'number' && !isNaN(initialStep)) {
      $stepper.addClass('active-step-' + (initialStep + 1));
    }

    jQuery('.wizard-next-btn').on('click', function (e) {
      e.preventDefault();
       $stepper.smartWizard("next");
    });
    $stepper.on("showStep", function (e, anchorObject, stepIndex) {
      $stepper.removeClass(function (index, className) {
        return (className.match(/(^|\s)active-step-\d+/g) || []).join(' ');
      });

      if (typeof stepIndex === 'number' && !isNaN(stepIndex)) {
        $stepper.addClass('active-step-' + (stepIndex + 1));
        console.log('Step Changed to:', stepIndex + 1);
      }
      setTimeout(() => {
        $stepper.smartWizard("fixHeight");
      }, 50);
    });
  }

  // Send page stepper JS
  if (jQuery('#send-page-stepper').length) {
    jQuery('#send-page-stepper').smartWizard({
      lang: {
        next: 'Next',
        previous: 'Back'
      },
      keyboard: false,
      enableUrlHash: false,
      anchor: {
        enableDoneStateNavigation: false // Enable/Disable the done state navigation
      },
    });
    jQuery('#send-page-stepper .sw-btn-next').addClass('primary-btn small-btn');
    jQuery('#send-page-stepper .sw-btn-prev').addClass('outline-btn');

    // Function to update prev-active classes
    function updatePrevActive(stepIndex) {
      const items = jQuery('#send-page-stepper .nav-item');
      items.removeClass('prev-active');
      items.each(function (index) {
        if (index < stepIndex) {
          jQuery(this).addClass('prev-active');
        }

        // TODO: Need to confirm
        if (index > stepIndex) {
          jQuery(this).find('.nav-link').removeClass('active');
          jQuery(this).find('.nav-link').removeClass('done');
        }
      });
    }

    function onStepChange(stepIndex) {
      const tabActive = jQuery('#send-page-stepper .tab-pane');
      tabActive.removeClass('active-tab');
      tabActive.find('.full-min-height-white-card').removeAttribute('style');
      tabActive.each(function (index) {
        if (index == stepIndex) {
          jQuery(this).addClass('active-tab');
          var whiteCardOffSet = jQuery('.full-min-height-white-card').offset().top;
          var windowHeight = jQuery(window).height() - 22;
          var fullHeightCard = windowHeight - whiteCardOffSet;
          var content = jQuery(this).find('.content-outer-wrapper').outerHeight + 50;
          if (content > fullHeightCard) {
            jQuery('.active-tab').find('.full-min-height-white-card').css('min-height', content + 'px');
          }
          else {
            jQuery('.active-tab').find('.full-min-height-white-card').css('min-height', fullHeightCard + 'px');
          }
        }
      });
    }

    // Initial run on load
    const initialIndex = jQuery('#send-page-stepper .nav-item .nav-link.active').parent().index();
    updatePrevActive(initialIndex);

    // Run on step change
    jQuery('#send-page-stepper').on("showStep", function (e, anchorObject, stepIndex) {
      updatePrevActive(stepIndex);
      // onStepChange(stepIndex);
      setTimeout(() => {
        jQuery('#send-page-stepper').smartWizard("fixHeight");
      }, 100);
    });
    setTimeout(() => {
      jQuery('#send-page-stepper').smartWizard("fixHeight");
    }, 500);
  }
  // send js end

  // import patient js start
  if (jQuery('#patient-import-stepper').length) {
    jQuery('#patient-import-stepper').smartWizard({
      lang: {
        next: 'Continue',
        previous: 'Cancel'
      },
    });
    jQuery('#patient-import-stepper .sw-btn-next').addClass('primary-btn small-btn');
    jQuery('#patient-import-stepper .sw-btn-prev').addClass('outline-btn');

    // Function to update prev-active classes
    function updatePrevActive(stepIndex) {
      const items = jQuery('#patient-import-stepper .nav-item');
      items.removeClass('prev-active');
      items.each(function (index) {
        if (index < stepIndex) {
          jQuery(this).addClass('prev-active');
        }
      });
    }
    const initialIndex = jQuery('#patient-import-stepper .nav-item .nav-link.active').parent().index();
    updatePrevActive(initialIndex);
    jQuery('#patient-import-stepper').on("showStep", function (e, anchorObject, stepIndex) {
      updatePrevActive(stepIndex);
    });
  }
  //import patient js end

  //File upload js start
  const dropZone = document.getElementById("dropZone");
  const fileInput = document.getElementById("fileInput");
  const fileList = document.getElementById("fileList");
  const browse = document.getElementById("browse");

  let uploadedFiles = [];
  if(jQuery('.patients-import-wrapper-outer').length && dropZone && browse && fileInput){
    browse.addEventListener("click", () => fileInput.click());

    dropZone.addEventListener("dragover", (e) => {
      e.preventDefault();
      dropZone.classList.add("hover");
    });

    dropZone.addEventListener("dragleave", () => {
      dropZone.classList.remove("hover");
    });

    dropZone.addEventListener("drop", (e) => {
      e.preventDefault();
      dropZone.classList.remove("hover");
      handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener("change", (e) => {
      handleFiles(e.target.files);
    });

    function handleFiles(files) {
      const validTypes = [
        "text/csv",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      ];

      Array.from(files).forEach((file) => {
        if (
          validTypes.includes(file.type) ||
          file.name.endsWith(".csv") ||
          file.name.endsWith(".xls") ||
          file.name.endsWith(".xlsx")
        ) {
          // Avoid duplicates
          if (
            !uploadedFiles.some(
              (f) => f.name === file.name && f.size === file.size
            )
          ) {
            uploadedFiles.push(file);
          }
        } else {
          alert(`Invalid file: ${file.name}`);
        }
      });

      renderFileList();
    }

    function renderFileList() {
      fileList.innerHTML = "";

      uploadedFiles.forEach((file, index) => {
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

        const fileItem = document.createElement("div");
        fileItem.className = "file-item";
        fileItem.innerHTML = `
          <em class="close-icon"><img src="/front/images/close-file-icon.svg" alt="Cancle"></em>
          <div class="file-item-inner">
            <div class="icon"><img src="/front/images/csv-file-icon.png" alt="File"></div>
            <div class="file-name">
              <span>${file.name}</span>
              <span class="size">${fileSizeMB} MB</span>
            </div>
          </div>
          <button class="delete-btn" onclick="removeFile(${index})"><img src="/front/images/delete-icon-red.svg" alt="Delete"></button>
          <div class="progressbar-block">
            <div class="progress-bar">
                <div class="progress-fill" data-used="500" data-total="2000" style="width:64%"></div>
            </div>
            <p class="count">64%</p>
          </div>
        `;

        fileList.appendChild(fileItem);
      });
      setTimeout(() => {
        jQuery('#patient-import-stepper').smartWizard("fixHeight");
      }, 500);
    }

    window.removeFile = function (index) {
      uploadedFiles.splice(index, 1);
      renderFileList();
    };
  }
  // File upload js end

  // toast message js
  jQuery('.toast-btn').click(function(){
    jQuery('.toast-message-wrapper').addClass('show-toast');
    setTimeout(function () {
      jQuery('.toast-message-wrapper').removeClass('show-toast');
    }, 3000);
  })
  jQuery('.toast-message-wrapper .close-icon').click(function() {
    jQuery(this).closest('.toast-message-wrapper').removeClass('show-toast');
  });


  // Modal JS
  jQuery('.modal-btn').click(function (e) {
    e.preventDefault();
    jQuery("body,html").addClass('modal-open');

    var _this = jQuery(this).attr('data-link');

    var _currentModal = jQuery(".custom-modal[data-target='" + _this + "']");
    _currentModal.addClass("visible");

    setTimeout(function () {
      _currentModal.addClass("fadein");
    }, 125);
  });

  jQuery('.custom-modal,.modal-close,.modal-cancel').click(function (e) {
    e.preventDefault();

    const $modal = jQuery(this).closest(".custom-modal");

    // Check if modal has data-lock="true"
    if (($modal.data('lock') === true || $modal.data('lock') === 'true') &&
    jQuery(this).hasClass('custom-modal')) {
        return;
    }

    jQuery("body,html").removeClass('modal-open');
    var _this = jQuery(this)
    jQuery(this).closest(".custom-modal").removeClass("fadein");
    setTimeout(function () {
      resetModalForm($modal);
      _this.closest(".custom-modal").removeClass("visible");
    }, 125);
  });

  jQuery(".custom-modal .modal-inner-content").click(function (e) {
    e.stopPropagation();
  });



  // Header submenu
  jQuery('li.has-submenu a').click(function (event) {
    if (this.closest('li').classList.contains('disabled')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
  }
    var _this = jQuery(this);
    _this.closest('li').siblings().children('.submenu').slideUp(300);
    _this.closest('li').children('.submenu').slideToggle(300);
    _this.closest('li').siblings().removeClass('active');
    _this.closest('li').toggleClass('active');
    setTimeout(function(){
      _this.closest('li').siblings().children('.submenu').removeClass('active');
    _this.closest('li').children('.submenu').toggleClass('active');
    },310);
  });

  jQuery('.site-header .hamburger').click(function () {
    jQuery('body').toggleClass('open-sidebar');
  });
  jQuery('.sidebar-overlay').click(function () {
    jQuery('body').removeClass('open-sidebar');
  });

  // notification click
  jQuery('.site-header .notification-icon').click(function (e) {
    e.stopPropagation();
    jQuery('body').toggleClass('open-notification');
  })
  jQuery('.notification-outer-wrapper').click(function (e) {
    e.stopPropagation();
  });
  jQuery(document).on('click', function () {
    jQuery('body').removeClass('open-notification');
    jQuery('.threedot-menu-wrapper').removeClass('open-menu');
  });
  // notification click end


  // three dot menu
  jQuery(document).on('click', '.threedot-menu-wrapper', function (e) {
    e.stopPropagation();
  });
  jQuery(document).on('click', '.threedot-menu-wrapper .threedot-menu-link', function (e) {
    e.stopPropagation();
    const $wrapper = jQuery(this).closest('.threedot-menu-wrapper');
    if ($wrapper.closest('table').hasClass('has-only-two-row')) {
      const $cell = $wrapper.closest('.dt-layout-cell');
    }
    jQuery('.threedot-menu-wrapper').not(jQuery(this).closest('.threedot-menu-wrapper')).removeClass('open-menu');
    jQuery(this).closest('.threedot-menu-wrapper').toggleClass('open-menu');    
  });
  jQuery('.tampate-popup .has-menu .primary-btn').click( function (e) {
    e.stopPropagation();
    jQuery(this).closest('.has-menu').toggleClass('open-menu');
  });
  jQuery('.tampate-popup .modal-dialog .modal-inner-content').click( function (e) {
    jQuery('.tampate-popup .has-menu').removeClass('open-menu');
  });

  // send show hide
  jQuery('.overdue-checkbox').change(function () {
    if (jQuery(this).is(':checked')) {
      jQuery('.overdue-sub-checkbox-wrapper').addClass('show-overdue');
    } else {
      jQuery('.overdue-sub-checkbox-wrapper').removeClass('show-overdue');
    }
    setTimeout(() => {
      jQuery('#send-page-stepper').smartWizard("fixHeight");
    }, 500);
  });
  jQuery('.promotionoal-checkbox').change(function () {
    if (jQuery(this).is(':checked')) {
      jQuery('.promotional-block-inner').addClass('show-overdue');
    } else {
      jQuery('.promotional-block-inner').removeClass('show-overdue');
    }
    setTimeout(() => {
      jQuery('#send-page-stepper').smartWizard("fixHeight");
    }, 500);
  });
  // send show hide end

  // datepicker start
  if (jQuery('.datepicker-wrapper').length) {
    jQuery("#inline-datepicker").flatpickr({
      dateFormat: "d M, Y",
      showMonths: 1,
      inline:true,
      onDayCreate: function (dObj, dStr, fp, dayElem) {
        if (!dayElem.classList.contains("flatpickr-day")) return;

        const dayDate = dayElem.dateObj;
        const monthShown = fp.currentMonth;
        const yearShown = fp.currentYear;

        // Check if the date belongs to another month
        if (dayDate.getMonth() !== monthShown || dayDate.getFullYear() !== yearShown) {
          dayElem.classList.add("flatpickr-disabled");
          dayElem.classList.add("notAllowed");
          dayElem.removeAttribute("aria-label");
        }
      }
    });
    jQuery(".custom-datepicker").each(function () {
      const _input = jQuery(this);
      const customFormat = _input.data("date-format") || "d M, Y"; // fallback to default format

      _input.flatpickr({
        dateFormat: customFormat,
        showMonths: 1,
        onDayCreate: function (dObj, dStr, fp, dayElem) {
          if (!dayElem.classList.contains("flatpickr-day")) return;

          const dayDate = dayElem.dateObj;
          const monthShown = fp.currentMonth;
          const yearShown = fp.currentYear;

          if (dayDate.getMonth() !== monthShown || dayDate.getFullYear() !== yearShown) {
            dayElem.classList.add("flatpickr-disabled", "notAllowed");
            dayElem.removeAttribute("aria-label");
          }
        }
      });
    });

    // date range picker
    let tempRange = [];
    const fp = $("#daterangepicker").flatpickr({
      mode: "range",
      dateFormat: "d M, Y",
      showMonths: 2,
      clickOpens: true,
      allowInput: false,
      maxDate: "today",
      onDayCreate: function (dObj, dStr, fp, dayElem) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const dayDate = new Date(dayElem.dateObj);
        dayDate.setHours(0, 0, 0, 0);

        if (dayDate > today) {
            dayElem.classList.add("flatpickr-disabled");
            dayElem.classList.add("notAllowed");
            dayElem.removeAttribute("aria-label");
            dayElem.style.opacity = "0.4";
        }
    },
      onChange: function (selectedDates, dateStr, instance) {
        tempRange = selectedDates;

        if (selectedDates.length === 2) {
          const formatted = instance.formatDate(selectedDates[0], "d M, Y") + " - " +
            instance.formatDate(selectedDates[1], "d M, Y");
          instance.input.value = formatted;
        }
      },
      onReady: function (selectedDates, dateStr, instance) {
        // Append Apply/Cancel buttons to the calendar container
        const customBtnHTML = `
          <div class="custom-buttons">
            <button type="button" class="cancel-btn border-btn">Cancel</button>
            <button type="button" class="apply-btn primary-btn small-btn">Apply</button>
          </div>
        `;
        $(instance.calendarContainer).append(customBtnHTML);

        // Cancel Button Click
        $(instance.calendarContainer).on("click", ".cancel-btn", function () {
          instance.clear();
          tempRange = [];
          instance.close();
        });

        // Apply Button Click
        $(instance.calendarContainer).on("click", ".apply-btn", function () {
          if (tempRange.length) {
            const formatted = tempRange.map(date =>
              instance.formatDate(date, "d M, Y")
            ).join(" - ");
            $("#daterangepicker").val(formatted);
          }
          instance.close();
        });
      }
    });
  }
  // datepicker end

  // Timepicker
  if (jQuery('.custom-timepicker').length) {
    jQuery('.custom-timepicker').each(function(){
      console.log('timepicker')
      let parent = jQuery(this).closest('.form-group');
      jQuery(this).flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        appendTo: document.querySelector('.time-picker-wrapper'),
      });
    });
  }

  // Time picker end

  // Progressbar
  // jQuery('.progressbar-block').each(function () {
  //   const fill = jQuery(this).find('.fill-blue');
  //   const fillOrange = jQuery(this).find('.fill-orange');
  //   const used = parseInt(fill.data('used'));
  //   const total = parseInt(fill.data('total'));
  //   const percent = (used / total) * 100;
  //   const orangeWidth =
  //   fill.css('width', percent + '%');
  //   fillOrange.css('left', percent + '%');
  //   jQuery(this).find('.sms-count span').text(`${used} / ${total}`);
  // });
  jQuery('.progressbar-block').each(function () {
    const $block = jQuery(this);
    const $blue = $block.find('.fill-blue');
    const $orange = $block.find('.fill-orange');

    const usedBlue = parseInt($blue.data('used'), 10);
    const usedOrange = parseInt($orange.data('used'), 10);
    const total = parseInt($blue.data('total'), 10);

    const percentBlue = (usedBlue / total) * 100;
    const percentOrange = (usedOrange / total) * 100;

    $blue.css({width: percentBlue + '%' })
    $orange.css({ width: (percentOrange) + '%', left: percentBlue + '%' })

    // Update initial count
    $block.find('.sms-count span').text(`${usedBlue + usedOrange } / ${total}`);
  });



  // Tabbing
  jQuery('.tab-menu ul li a').click(function (e) {
    e.preventDefault();
    jQuery(this).closest('.custom-tab-wrapper').find('>.tab-search-wrapper .tab-menu li').removeClass('tab-active');
    jQuery(this).closest('li').addClass('tab-active');
    var tabLink = jQuery(this).data('link');
    jQuery(this).closest('.custom-tab-wrapper').find('>.tab-content-wrapper>.tab-content').removeClass('tab-active');
    jQuery(this).closest('.custom-tab-wrapper').find('.tab-content[data-tab="' + tabLink + '"]').addClass('tab-active');
    if (jQuery('.custom-table-wrapper').length) {
      jQuery('.custom-table-wrapper table:visible').each(function () {
        if (jQuery.fn.dataTable.isDataTable(this)) {
          jQuery(this).DataTable().columns.adjust().draw();
        }
      });
    }

  });

  // datatable initilize
  initializeCustomTables();


  jQuery(document).on('click', '.template-accordion-parent .template-accordion .template-accordion-title', function () {
    const $accordion = jQuery(this).closest('.template-accordion');
    $accordion.find('.template-accordion-content').slideToggle(500);
    $accordion.siblings().find('.template-accordion-content').slideUp(500);
    setTimeout(function(){
      $accordion.toggleClass('active');
      $accordion.siblings().removeClass('active');
    },550);
  });


  // insurance page
  if (jQuery('#preferred-insurance-table').length) {
    let preferredInsuranceTable = new DataTable('#preferred-insurance-table', {
        paging: false,
        info: false,
        searching: false,
        columns: [
          { orderable: true },  // Insurance Abbreviation
          { orderable: true },  // Name
          { orderable: false }, // Group
          { orderable: false },  // action
        ],
         language: {
             emptyTable: `<div class="empty-message"><p>No insurances found.</p></br>
             <p>To get started, go to Patients → Import Patients and upload your patient list.</p></br>
              <p>Once imported, your patients’ insurances will appear here.</p></div>`
        }
    });
  }
  if (jQuery('#other-insurance-table').length) {
      let otherInsuranceTable = new DataTable('#other-insurance-table', {
          paging: false,
          info: false,
          searching: false,
          columns: [
            { orderable: true },  // Insurance Abbreviation
            { orderable: true },  // Name
            { orderable: false }, // Group
            { orderable: false },  // action
          ],
           language: {
             emptyTable: `<div class="empty-message"><p>No insurances found.</p></br>
             <p>To get started, go to Patients → Import Patients and upload your patient list.</p></br>
              <p>Once imported, your patients’ insurances will appear here.</p></div>`

          }
      });
  }

  // activity page
  if (jQuery('#upcoming-campaigns-table').length) {
    function upcomingCampaignsTableFormat(d) {
      let template = document.querySelector('#overdue-recalls-row-template');
      return template.innerHTML;
    }
    let upcomingCampaignsTable = new DataTable('#upcoming-campaigns-table', {
      paging: true,
      pageLength: 10,
      lengthChange: false,
      searching: false,
      info: true,
      ordering: false,
      responsive: true,
      language: {
        info: "Showing _START_ of _TOTAL_ Results",
        paginate: {
          previous: '<img src="/front/images/prev-arrow.svg">',
          next: '<img src="/front/images/next-arrow.svg">'
        }
      },
      layout: {
        bottomEnd: {
          paging: {
            firstLast: false
          }
        }
      }
    });

    upcomingCampaignsTable.on('click', 'td.dt-control', function (e) {
      let tr = e.target.closest('tr');
      let row = upcomingCampaignsTable.row(tr);
      if (row.child.isShown()) {
        // row.child.hide();
          row.child().find('.details-wrapper').slideUp(500, function () {
          setTimeout(function () {
            row.child.hide();
          }, 100); // delay in ms before hide is executed
        });
      } else {
        // row.child(upcomingCampaignsTableFormat(row.data()), 'custom-child-row').show();
         // Create hidden wrapper around content
         const childHtml = `
            <div class="details-wrapper" style="display:none;">
              ${upcomingCampaignsTableFormat(row.data())}
            </div>`;
          row.child(childHtml, 'custom-child-row').show();
          row.child().find('.details-wrapper').slideDown(500);
      }
    });
  }

  if (jQuery('#completed-campaigns-table').length) {    
    function completedCampaignsTableFormat(d) {
      let template = document.querySelector('#completed-campaigns-row-template');
      return template.innerHTML;
    }
    let completedCampaignsTable = new DataTable('#completed-campaigns-table', {
      paging: true,
      pageLength: 10,
      lengthChange: false,
      searching: false,
      info: true,
      ordering: false,
      responsive: true,
      language: {
        info: "Showing _START_ of _TOTAL_ Results",
        paginate: {
          previous: '<img src="/front/images/prev-arrow.svg">',
          next: '<img src="/front/images/next-arrow.svg">'
        }
      },
      layout: {
        bottomEnd: {
          paging: {
            firstLast: false
          }
        }
      }
    });
    completedCampaignsTable.on('click', 'td.dt-control', function (e) {      
      let tr = e.target.closest('tr');
      let row = completedCampaignsTable.row(tr);
      if (row.child.isShown()) {
        // row.child.hide();
        row.child().find('.details-wrapper').slideUp(500, function () {
          row.child.hide(); // only after animation finishes
        });
      } else {
        // row.child(completedCampaignsTableFormat(row.data()), 'custom-child-row').show();
         // Create hidden wrapper around content
         const childHtml = `
            <div class="details-wrapper" style="display:none;">
              ${completedCampaignsTableFormat(row.data())}
            </div>`;
          row.child(childHtml, 'custom-child-row').show();
          row.child().find('.details-wrapper').slideDown(500);
      }
    });
  }

  // subscription page
  if(jQuery('#billing-history-table').length){
    jQuery('#billing-history-table').dataTable({
      paging: false,
      searching: false,
      ordering: false,
      info: false,
    })
  }
  // import patient page
  if (jQuery('#preview-and-confirm').length) {
    jQuery('#preview-and-confirm').dataTable({
      paging: true,
      pageLength: 10,
      lengthChange: false,
      searching: false,
      info: true,
      ordering: false,
      responsive: true,
      language: {
        info: "Showing _START_ of _TOTAL_ Results",
        paginate: {
          previous: '<img src="/front/images/prev-arrow.svg">',
          next: '<img src="/front/images/next-arrow.svg">'
        }
      },
      layout: {
        bottomEnd: {
          paging: {
            firstLast: false
          }
        }
      }
    });
  }
});

if(jQuery('.progressbar-wrapper').length) {
  const circle = document.querySelector('.progress');
  const text = document.querySelector('.percentage');
  const input = document.getElementById('progressInput');

  const radius = circle.r.baseVal.value;
  const circumference = 2 * Math.PI * radius;

  circle.style.strokeDasharray = `${circumference} ${circumference}`;
  circle.style.strokeDashoffset = circumference;

  function setProgress(percent) {
      const offset = circumference - (percent / 100) * circumference;
      circle.style.strokeDashoffset = offset;
      text.textContent = `${percent}%`;
  }

  input.addEventListener('input', () => {
      const value = +input.value;
      setProgress(value);
  });
  setProgress(input.value);
}


// file upload and drage drop JS
function setupUpload(boxId, inputId, labelId) {
  const box = document.getElementById(boxId);
  const input = document.getElementById(inputId);
  const label = document.getElementById(labelId);

  box?.addEventListener("click", () => input.click());

  box?.addEventListener("dragover", (e) => {
    e.preventDefault();
    box.classList.add("dragover");
  });

  box?.addEventListener("dragleave", () => {
    box.classList.remove("dragover");
  });

  box?.addEventListener("drop", (e) => {
    e.preventDefault();
    box.classList.remove("dragover");
    input.files = e.dataTransfer.files;
    handlePreview(input, box, label);
  });

  input?.addEventListener("change", () => {
    handlePreview(input, box, label);
  });
}

function handlePreview(input, box, label) {
  updateContinueButton();
  const file = input.files[0];
  let parentEle = box.closest('.form-group');
  const node = document.createElement('div');
  node.className = 'remove-btn';
  node.textContent = 'Remove';
  if (file && file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = () => {
      box.innerHTML = `
      <img src="${reader.result}" alt="Preview">
    `;
      if (!parentEle.querySelector('.remove-btn')) {
        parentEle.appendChild(node);
      }
      parentEle.querySelector(".remove-btn")?.addEventListener("click", () => {
        const hiddenInput = input.id === 'front-input' ? document.getElementById('front-hidden') : document.getElementById('back-hidden');

        hiddenInput.value = '';
        input.value = "";
        box.innerHTML = "";
        box.appendChild(input);
        box.appendChild(label);
        parentEle.removeChild(node);
        updateContinueButton();
        setTimeout(() => {
          jQuery('#secure-account-stepper').smartWizard("fixHeight");
        }, 50);
      });
      // updateContinueButton();
    };
    reader.readAsDataURL(file);
  }
  setTimeout(() => {
    jQuery('#secure-account-stepper').smartWizard("fixHeight");
  }, 50);

}

function updateContinueButton() {
  const frontInput = document.getElementById("front-input");
  const backInput = document.getElementById("back-input");

  const frontFilled = frontInput?.files?.length > 0;
  const backFilled = backInput?.files?.length > 0;

  const btn = document.getElementById("finish-btn");
  console.log("Front filled:", frontFilled, "Back filled:", backFilled);

  if (frontFilled && backFilled) {
    btn.classList.add("active");
  } else {
    btn.classList.remove("active");
  }
}

setupUpload("front-box", "front-input", "front-label");
setupUpload("back-box", "back-input", "back-label");

jQuery(window).resize(function () {
  setTimeout(function () {
    adjustPadding();
    fullHeightWhiteCard();
    fullMinHeightWhiteCard();
  }, 300)
  moveMainTitle();
});

function escapeHtml(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

