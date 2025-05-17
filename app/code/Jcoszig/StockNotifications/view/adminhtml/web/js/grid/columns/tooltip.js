define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Jcoszig_StockNotifications/grid/cells/tooltip'
        },

        /**
         * Get label for the column
         *
         * @param {Object} row
         * @returns {String}
         */
        getLabel: function (row) {
            return row[this.index] ? row[this.index].content : '';
        },

        /**
         * Get tooltip content
         *
         * @param {Object} row
         * @returns {String}
         */
        getTooltip: function (row) {
            return row[this.index] ? row[this.index].tooltip : '';
        }
    });
});
