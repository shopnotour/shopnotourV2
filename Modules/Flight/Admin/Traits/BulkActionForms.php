<?php

namespace Modules\Flight\Admin\Traits;

/**
 * BulkActionForms Trait
 *
 * Generates HTML forms for bulk operations
 * Used in DiscountController
 */
trait BulkActionForms
{
    /**
     * Generate bulk action form HTML based on action type
     */
    public function generateFormHtml($action, $count, $gdsOptions)
    {
        switch($action) {
            case 'delete':
                return $this->getDeleteForm($count);
            case 'copy':
                return $this->getCopyForm($count, $gdsOptions);
            case 'status':
                return $this->getStatusForm($count);
            case 'update-valid-dates':
                return $this->getValidDatesForm($count);
            case 'change-source':
                return $this->getChangeSourceForm($count, $gdsOptions);
            default:
                return '<div class="alert alert-danger">Invalid action</div>';
        }
    }

    /**
     * Delete confirmation form
     */
    private function getDeleteForm($count)
    {
        return <<<HTML
        <div class="alert alert-danger">
            <h5><i class="fa fa-exclamation-triangle"></i> {{__('Delete Confirmation')}}</h5>
            <p>{{__('Are you sure you want to delete')}} <strong>$count</strong> {{__('discount(s)?')}}</p>
            <p class="text-muted mb-0">{{__('This action cannot be undone.')}}</p>
        </div>
        HTML;
    }

    /**
     * Bulk copy form
     */
    private function getCopyForm($count, $gdsOptions)
    {
        $gdsOptionsHtml = '<option value="keep">{{__("Keep Original")}}</option>';

        if (!empty($gdsOptions)) {
            foreach ($gdsOptions as $key => $value) {
                $gdsOptionsHtml .= '<option value="' . e($key) . '">' . e($value) . '</option>';
            }
        }

        return <<<HTML
        <div>
            <p class="mb-3">{{__('Will copy')}} <strong>$count</strong> {{__('discount(s). New codes will be auto-generated with -COPY suffix.')}}</p>

            <div class="form-group">
                <label for="gds_type">{{__('GDS/Source')}}</label>
                <select name="gds_type" id="gds_type" class="form-control">
                    $gdsOptionsHtml
                </select>
                <small class="form-text text-muted">
                    {{__('Select "Keep Original" to maintain the same GDS type for copied discounts.')}}
                </small>
            </div>

            <div class="alert alert-info">
                <h6>{{__('Copying Details:')}}</h6>
                <ul class="mb-0">
                    <li>{{__('New discount code will be generated with -COPY suffix')}}</li>
                    <li>{{__('All other fields will be identical to original')}}</li>
                    <li>{{__('Status will be set to inactive')}}</li>
                    <li>{{__('You can edit individual copies after creation')}}</li>
                </ul>
            </div>
        </div>
        HTML;
    }

    /**
     * Bulk status change form
     */
    private function getStatusForm($count)
    {
        return <<<HTML
        <div>
            <p class="mb-3">{{__('Change status for')}} <strong>$count</strong> {{__('discount(s).')}}</p>

            <div class="form-group">
                <label for="status">{{__('New Status')}}</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="">{{__('Select Status')}}</option>
                    <option value="active">{{__('Active')}}</option>
                    <option value="inactive">{{__('Inactive')}}</option>
                </select>
            </div>

            <div class="alert alert-info">
                <strong>{{__('Note:')}} </strong> {{__('All selected discounts will be changed to the selected status.')}}
            </div>
        </div>
        HTML;
    }

    /**
     * Bulk valid dates form
     */
    private function getValidDatesForm($count)
    {
        return <<<HTML
        <div>
            <p class="mb-3">{{__('Update valid dates for')}} <strong>$count</strong> {{__('discount(s).')}}</p>

            <div class="form-group">
                <label for="valid_from">{{__('Valid From')}}</label>
                <input type="date" name="valid_from" id="valid_from" class="form-control">
                <small class="form-text text-muted">
                    {{__('Leave empty to keep existing dates')}}
                </small>
            </div>

            <div class="form-group">
                <label for="valid_to">{{__('Valid To')}}</label>
                <input type="date" name="valid_to" id="valid_to" class="form-control">
                <small class="form-text text-muted">
                    {{__('Leave empty to keep existing dates')}}
                </small>
            </div>

            <div class="alert alert-warning">
                <strong>{{__('Important:')}}</strong>
                <ul class="mb-0">
                    <li>{{__('Only fill the dates you want to change')}}</li>
                    <li>{{__('End date must be after start date')}}</li>
                    <li>{{__('Existing dates will be preserved if left empty')}}</li>
                </ul>
            </div>
        </div>
        HTML;
    }

    /**
     * Bulk change source form
     */
    private function getChangeSourceForm($count, $gdsOptions)
    {
        $gdsOptionsHtml = '<option value="">{{__("Select GDS Type")}}</option>';

        if (!empty($gdsOptions)) {
            foreach ($gdsOptions as $key => $value) {
                $gdsOptionsHtml .= '<option value="' . e($key) . '">' . e($value) . '</option>';
            }
        }

        return <<<HTML
        <div>
            <p class="mb-3">{{__('Change GDS/Source for')}} <strong>$count</strong> {{__('discount(s).')}}</p>

            <div class="form-group">
                <label for="gds_type">{{__('GDS Type')}}</label>
                <select name="gds_type" id="gds_type" class="form-control" required>
                    $gdsOptionsHtml
                </select>
            </div>

            <div class="alert alert-info">
                <strong>{{__('Available GDS Types:')}}</strong>
                <ul class="mb-0">
                    <li><strong>Travelport:</strong> {{__('Galileo, Apollo, Worldspan')}}</li>
                    <li><strong>Sabre:</strong> {{__('Sabre distribution system')}}</li>
                </ul>
            </div>
        </div>
        HTML;
    }
}
