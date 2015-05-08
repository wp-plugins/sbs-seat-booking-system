<div class="modal fade bs-example-modal-lg" id="myModal" tabindex="-1" role="dialog"  aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" >
    <div class="modal-content">
      <div class="modal-header">
        <!--<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>-->
        <h4 class="modal-title" id="myModalLabel">Schedule for this object</h4>
      </div>
      <div class="modal-body">
          <div id='description_panel'></div>
          <div class="clear"></div>
          
          <div class="wrp">
            <div id='calendar'></div>
          </div>
      </div>
      <div class="modal-footer">
          <input type="hidden" id="resource_id_temp">
          <input type="hidden" id="widget_id_temp">
        <button type="button" class="btn btn-default" id="close_modal">Close</button>
        <button data="" type="button" id="book_this" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>