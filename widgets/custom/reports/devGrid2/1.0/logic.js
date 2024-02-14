RightNow.namespace('Custom.Widgets.reports.devGrid2');
Custom.Widgets.reports.devGrid2 = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
        this._currentPage = 1;
                this._back = this.Y.one(this.baseSelector + ' .back');
                this._forward = this.Y.one(this.baseSelector + ' .forward');
                this._paginator = this.Y.all(this.baseSelector + ' .pagination li a'); //arreglo con todos los botones del paginador
                if (this.data.js.total_pages <= 0){
                    this._forward.addClass('disableClick');
                    this._back.addClass('disableClick');
                    this._currentPage = 0;
                } else if(this.data.js.total_pages == 1) {
                    this._forward.addClass('disableClick');
                    this._back.addClass('disableClick');
                } else {
                    for(var i = 1; i <= this.data.js.total_pages; i++)
                    {
                        var pageLinkID = this.baseSelector + ' .page'+i;
                        this.Y.one(pageLinkID).on('click', this._onPageChange, this, i);
                    }
                }
                this._forward.on('click', this._onClickForward, this, true);
                this._back.on('click', this._onClickBack, this, true);
                //this._hidePages();
        },
        _onClickForward: function() {
            //metodo subscrito al boton (a tag) forward, que permite avanzar clickeando el boton
            if (this._currentPage >= 1 && this._currentPage < this.data.js.total_pages) {
                this.Y.one(this.baseSelector + ' .page'+this._currentPage).removeClass('pageSelected');
                this._currentPage++;
                this.Y.one(this.baseSelector + ' .page'+this._currentPage).addClass('pageSelected');
                this._hidePages(this._currentPage);
                this.getDefault_ajax_endpoint(this._currentPage);
            }
        },
        _onClickBack: function() {
            //metodo subscrito al boton (a tag) back, que permite retroceder clickeando el boton
            if (this._currentPage > 1) {
                this.Y.one(this.baseSelector + ' .page'+this._currentPage).removeClass('pageSelected');
                this._currentPage--;
                this.Y.one(this.baseSelector + ' .page'+this._currentPage).addClass('pageSelected');
                this._hidePages(this._currentPage);
                this.getDefault_ajax_endpoint(this._currentPage);
            }
        },
        _onPageChange: function(i)
        {
            //metodo onclick subscrito a todos los elementos page1, page2, ..., page n de la vista
            var pageNumber = i._currentTarget.className.split('page')[1];
            if (this._currentPage != pageNumber){
                this._cleanPageSelected();
                this._currentPage = pageNumber;
                this.Y.one(this.baseSelector + ' .page'+this._currentPage).addClass('pageSelected');
                this._hidePages(pageNumber);
                this.getDefault_ajax_endpoint(pageNumber);
            }else if(!this.Y.one(this.baseSelector + ' .page'+this._currentPage).hasClass('pageSelected')){
                this._cleanPageSelected();
                this.Y.one(this.baseSelector + ' .page'+this._currentPage).addClass('pageSelected');
                this._hidePages(pageNumber);
                this.getDefault_ajax_endpoint(pageNumber);
            }
        },
        _cleanPageSelected: function()
        {
            //limpiar la clase pageselected del array
            this._paginator.removeClass('pageSelected');
        },
        _hidePages: function(currentPage)
        {
            for(var i = 1; i <= this.data.js.total_pages; i++)
            {
                if(this._shouldShowPageNumber(i,currentPage,this.data.js.total_pages))
                    this.Y.one(this.baseSelector + ' .paginator'+i).removeClass('rn_HiddenPaginator');
                else
                    this.Y.one(this.baseSelector + ' .paginator'+i).addClass('rn_HiddenPaginator');
            }
        },

        /**
         * Determines if a hellip should be displayed.
         * @param {integer} pageNumber Page number to check
         * @param {integer} currentPage Current/clicked page number
         * @param {integer} endPage Last page number in the pagination
         * @return {bool} True if the hellip should be displayed
         */
        _shouldShowHellip: function(pageNumber, currentPage, endPage) {
            return Math.abs(pageNumber - currentPage) === ((currentPage === 1 || currentPage === endPage) ? 3 : 2);
        },

        /**
         * Determines if the given page number should be displayed.
         * The pagination pattern followed here is:
         *     1 ... 4 5 6 ... 12.
         * if, for example, 5 is the current/clicked page out of a total of 12 pages.
         * @param {integer} pageNumber Page number to check
         * @param {integer} currentPage Current/clicked page number
         * @param {integer} endPage Last page number in the pagination
         * @return {bool} True if the page number should be displayed.
         */
        _shouldShowPageNumber: function(pageNumber, currentPage, endPage) {
            return pageNumber === 1 || (pageNumber === endPage) || (Math.abs(pageNumber - currentPage) <= ((currentPage === 1 || currentPage === endPage) ? 2 : 1));
        },
        /**
         * Makes an AJAX request for `default_ajax_endpoint`.
         */
        getDefault_ajax_endpoint: function(pageNumber) {
            // Make AJAX request:
            var eventObj = {data:{
                page: parseInt(pageNumber,10),
                report_id: this.data.attrs.report_id,
                per_page: this.data.attrs.per_page,
                filters: this.data.attrs.filters,
                url_per_col: this.data.attrs.url_per_col,
                col_id_url: this.data.attrs.col_id_url
                }};

            RightNow.Ajax.makeRequest(this.data.attrs.default_ajax_endpoint, eventObj.data, {
                successHandler: this.default_ajax_endpointCallback,
                scope:          this,
                data:           eventObj,
                json:           true
            });
        },

        /**
         * Handles the AJAX response for `default_ajax_endpoint`.
         * @param {object} response JSON-parsed response from the server
         * @param {object} originalEventObj `eventObj` from #getDefault_ajax_endpoint
         */
        default_ajax_endpointCallback: function(response, originalEventObj) {
            // Handle response
            var table = this.Y.all(this.baseSelector + ' table tbody tr');
            table.remove();
            var newTable = this.Y.one(this.baseSelector + ' table tbody');
            for (i=0; i<response.result.data.length; i++ )
            {
                newTable.appendChild(this.Y.Node.create('<tr class="col'+i+'">'));

                var node = this.Y.one(this.baseSelector + ' .col'+i);

                for ( j=0; j<response.result.headers.length; j++ )
                {
                    if (response.result.headers[j].data_type == 99)
                        node.appendChild(this.Y.Node.create('<td class="url" data-title="'+response.result.headers[j].heading+'">'+'<a href="'+this.data.attrs.url_per_col+response.result.data[i][j]+'">'+response.result.data[i][j]+'</td>'));
                    else
                        node.appendChild(this.Y.Node.create('<td class="data" data-title="'+response.result.headers[j].heading+'">'+response.result.data[i][j]+'</td>'));
                    if (j == response.result.headers.length - 1){
                        node.append(this.Y.Node.create('</tr>'));
                    }
                }
            }
        }
});