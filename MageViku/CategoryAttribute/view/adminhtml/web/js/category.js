define(['jquery'], function ($) {
    'use strict';
    return function (config) {
        $(document).ready(function($) {
            //$('#category-tree li ul').hide();

            $("#expandAll").click(function() {
                $('.child-categories').show();
                $('.x-tree-ec-icon').removeClass("x-tree-elbow-plus").addClass("x-tree-elbow-minus");
            });
            $("#collapseAll").click(function() {
                $('.child-categories').hide();
                $('.x-tree-ec-icon').removeClass("x-tree-elbow-minus").addClass("x-tree-elbow-plus");
            });
            // Expand/collapse functionality
            $(document).on("click", ".x-tree-ec-icon", function() {
                var $icon = $(this);
                if ($icon.hasClass("x-tree-elbow-plus")) {
                    $icon.removeClass("x-tree-elbow-plus").addClass("x-tree-elbow-minus");
                    $icon.closest(".x-tree-node-el").siblings(".child-categories").show();
                } else {
                    $icon.removeClass("x-tree-elbow-minus").addClass("x-tree-elbow-plus");
                    $icon.closest(".x-tree-node-el").siblings(".child-categories").hide();
                }
            });
            //Submit valid form.
            $(config.formSubmitButtonSelector).click(function() {
                $(config.formSelector).submit();
            });

            // Check/uncheck parent category to affect children
            $(config.catCheckboxSelector).change(function() {
                var checked = $(this).prop('checked');
                if(checked){
                    $(config.formSubmitButtonSelector).prop('disabled', !checked);
                }else{
                    $(config.formSubmitButtonSelector).prop('disabled', !checked);
                }
                $(this).closest(".x-tree-node-el").siblings('ul').find('.category-checkbox').prop('checked', checked);
            });
        });
    }
})
