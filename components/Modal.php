<!-- Modal Scada -->
<div class="modal fade bd-example-modal-md" id="ScadamyModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="TimeSelect"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="./TableRespone.php?table=tablescada" method="post" id="fromScada">
                    <input type="hidden" id="period" name="period" />
                    <input type="hidden" id="tagnamescada" name="tagnamescada">
                    <input type="hidden" id="addtag" name="addtag">
                    <input type="hidden" id="id_tmp" name="id_tmp">
                    <div class="form-group" style="display:block;" id="template_select">
                        <label for="cboTemplate1" class="col-form-label">Template :</label>
                        <select name="cboTemplate1" id="cboTemplate1">
                            <?php
                            echo $SCADA->loadComboNullSCADA(
                                "SELECT TMPL_ID,TMPL_DESC FROM TEMPLATES WHERE TMPL_OWNER = '" . $gUserName . "' OR PUBLIC_FLAG = 'Y' ORDER BY TMPL_DESC",
                                "TMPL_DESC",
                                "TMPL_ID",
                                $tmplID
                            );
                            ?>
                        </select>
                    </div>
                    <div class="form-group" id="sh_tag_div" style="display:none;">
                        <label for="message-text" class="col-form-label">Search Tag by keyword : 
                        </label>&nbsp;<label style="color:red;">
                            Minimum two characters*</label>
                        </label>
                        <input type="text" class="form-control" id="sh_tag" name="sh_tag" list="tagname">
                            
                    </div>
                    <div class="form-group" id="AddTagName" style="display:none;">
                        <label for="message-text" class="col-form-label">Search Tag by RTU :</label>
                        <select name="cboTemplate_RTU" id="cboTemplate_RTU">
                            <?php
                            echo $SCADA->loadComboNullRTU("select distinct RTU from DUMP_UNIT_HIST order by RTU", "RTU", "RTU");
                            ?>
                        </select>
                    </div>
                    <div class="form-group" id="displayTemp" style="display:block;">
                        <label for="message-text" class="col-form-label">Tag name :</label>
                        <textarea class="form-control" id="message-text" name="tagname"
                            style="height:100px;background-color:#FFF;cursor:no-drop;" readonly="true"></textarea>
                        <BR>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons" id="btngroup">
                            <label class="btn btn-success btn-sm" onclick="AddTag()">
                                <input type="radio" name="options" id="option1" autocomplete="off"> Add Tag
                            </label>
                            <label class="btn btn-warning btn-sm" onclick="ClearTag()">
                                <input type="radio" name="options" id="option2" autocomplete="off"> Clear Tag
                            </label>
                            <label class="btn btn-primary btn-sm" onclick="SaveTemplate()">
                                <input type="radio" name="options" id="option3" autocomplete="off"> Save Template
                            </label>
                        </div>
                    </div>
                    <div class="form-group" id="displayCondition" style="display:block;">
                        <div class="row">
                            <div class='col-md-12'>
                                <label for="recipient-name" class="col-form-label">Condition :</label><BR>
                                <div class="form-check form-check-inline">
                                    <div id='today'>
                                        <input class="form-check-input" type="radio" name="iCodition" value="curDate"
                                            onclick="SetTimeBLYAAAAAAAAAAT(this)">
                                        <label class="form-check-label">Today</label>
                                    </div>
                                </div>
                                <div class="form-check form-check-inline">
                                    <div id='l60min'>
                                        <input class="form-check-input" type="radio" name="iCodition" value="last60Min"
                                            onclick="SetTimeBLYAAAAAAAAAAT(this)">
                                        <label class="form-check-label">Last 60 minutes</label>
                                    </div>
                                </div>
                                <div class="form-check form-check-inline">
                                    <div id='l24hr'>
                                        <input class="form-check-input" type="radio" name="iCodition" value="last24Hour"
                                            onclick="SetTimeBLYAAAAAAAAAAT(this)">
                                        <label class="form-check-label">Last 24 hour</label>
                                    </div>
                                </div>
                                <div class="form-check form-check-inline">
                                    <div id='Yes'>
                                        <input class="form-check-input" type="radio" name="iCodition" value="prevDate"
                                            onclick="SetTimeBLYAAAAAAAAAAT(this)">
                                        <label class="form-check-label">Yesterday</label>
                                    </div>
                                </div>
                                <div id='day'>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="iCodition" value="curMonth"
                                            onclick="SetTimeBLYAAAAAAAAAAT(this)">
                                        <label class="form-check-label">Curmonth</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="iCodition" value="last7Day"
                                            onclick="SetTimeBLYAAAAAAAAAAT(this)">
                                        <label class="form-check-label">Last 7 days</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="iCodition" value="last30Day"
                                            onclick="SetTimeBLYAAAAAAAAAAT(this)" >
                                        <label class="form-check-label">Last 30 days</label>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-12'>
                                <label for="recipient-name" class="col-form-label">Display options :</label><BR>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iDisplay" value="Tagname"
                                        checked>
                                    <label class="form-check-label" for="i4">Tag name</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iDisplay" value="Description">
                                    <label class="form-check-label" for="i5">Description</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iDisplay"
                                        value="Tagnameanddescription">
                                    <label class="form-check-label" for="i6">Tag name and description</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="displayFromdate" style="display:block;">
                        <label class="col-form-label">From Date Time :</label>
                        <div class="row">
                            <div class='col-md-6'>
                                <input type="date" class="form-control" id="Fromdate" name="Fromdate" required>
                            </div>
                            <div class='col-md-6'>
                                <input type="time" class="form-control" id="Fromtime" name="Fromtime" min="00:00" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="displayTodate" style="display:block;">
                        <label class="col-form-label">To Date Time : </label>&nbsp;<label style="color:red;" id="Maxdays">
                            Maximum duration 35 days*</label>
                        <div class="row">
                            <div class='col-md-6'>
                                <input type="date" class="form-control" id="Todate" name="Todate" required>
                            </div>
                            <div class='col-md-6'>
                                <input type="time" class="form-control" id="Totime" name="Totime" min="00:00" required>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <div id="Start" style="display:block;">
                    <button type="submit" class="btn btn-primary btn-sm" onclick="submitScada()">Search</button>
                </div>
                </form>
                <div id="FootAdd" style="display:none;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="Cancle()">Cancle</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="SearchTAG()">Search</button>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- End Modal Scada -->
<!-- Modal GMDR -->
<div class="modal fade" id="GMDRmyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GMDRModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="./TableRespone.php?table=tablegmdr" method="post"  id="fromGmdr">
                    <input type="hidden" id="GMDRSETTABLE" name="GMDRSETTABLE">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Meter Name:</label>
                        <select name="cboTemplate2" id="cboTemplate2" required>
                            <?php
                            echo $SCADA->loadComboNullGMDR(
                                "select FC_NAME, FC_DESC as METER  from REF_FC_NAME",
                                "METER",
                                "FC_NAME"
                            );
                            ?>
                        </select>
                    </div>
                    <BR>
                    <BR>
                    <div class="form-group" id='GMDRHOUR' style="display:none;">
                        <div class="row">
                            <div class='col-md-12'>
                                <label for="recipient-name" class="col-form-label">Codition :</label><BR>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iCodition" value="curDate"
                                        onclick="SetTimeBLYAAAAAAAAAATGMDR(this)">
                                    <label class="form-check-label">Today</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iCodition" value="prevDate"
                                        onclick="SetTimeBLYAAAAAAAAAATGMDR(this)">
                                    <label class="form-check-label">Yesterday</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iCodition" value="last24Hour"
                                        onclick="SetTimeBLYAAAAAAAAAATGMDR(this)">
                                    <label class="form-check-label">Last 24 hour</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id='GMDRDAY' style="display:none;">
                        <div class="row">
                            <div class='col-md-12'>
                                <label for="recipient-name" class="col-form-label">Codition :</label><BR>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iCodition" value="curMonth"
                                        onclick="SetTimeBLYAAAAAAAAAATGMDR(this)">
                                    <label class="form-check-label">Curmonth</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iCodition" value="last7Day"
                                        onclick="SetTimeBLYAAAAAAAAAATGMDR(this)">
                                    <label class="form-check-label">Last 7 days</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iCodition" value="last30Day"
                                        onclick="SetTimeBLYAAAAAAAAAATGMDR(this)">
                                    <label class="form-check-label">Last 30 days</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="displayFromdate" style="display:block;">
                        <label class="col-form-label">From Date Time :</label>
                        <div class="row">
                            <div class='col-md-6'>
                                <input type="date" class="form-control" id="Fromdate-gmdr" name="Fromdate-gmdr"
                                    required>
                            </div>
                            <div class='col-md-6'>
                                <input type="time" class="form-control" id="Fromtime-gmdr" name="Fromtime-gmdr"
                                    min="00:00" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="displayTodate" style="display:block;">
                        <label class="col-form-label">To Date Time : </label>&nbsp;<label style="color:red;">
                            Maximum duration 35 days*</label>
                        <div class="row">
                            <div class='col-md-6'>
                                <input type="date" class="form-control" id="Todate-gmdr" name="Todate-gmdr" required>
                            </div>
                            <div class='col-md-6'>
                                <input type="time" class="form-control" id="Totime-gmdr" name="Totime-gmdr" min="00:00" required>
                            </div>
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm" onclick="submitGmdr()">Search</button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal GMDR -->
<!-- Modal Tag Config -->
<div class="modal fade" id="tagconfig" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tag Config</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="./Tagconfig.php" method="post">
                    <div class="form-group">
                        <div class="row">
                            <div class='col-md-12'>
                                <label for="recipient-name" class="col-form-label">Period : </label><BR>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Period" value="Daily" required>
                                    <label class="form-check-label">Daily</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Period" value="Hourly" required>
                                    <label class="form-check-label">Hourly</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Meter Name:</label>
                        <select name="cboTemplate3" id="cboTemplate3" required>
                            <?php
                            echo $SCADA->loadComboNullTagconfig(
                                "select FC_NAME, FC_DESC as METER  from REF_FC_NAME",
                                "METER",
                                "FC_NAME"
                            );
                            ?>
                        </select>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal Tag Config -->
<!-- Save Modal Template-->
<div class="modal fade" id="NewTemplate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">New Template</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="./auth/PostFile.php" method="post">
            <div class="modal-body">
                <div class='from-group'>
                    <label class="col-form-label">Template Name : </label>
                    <input type="text" class="form-control" id="tmpname" name="tmpname" required>
                    <label class="col-form-label">Selected Tag : </label>
                    <textarea 
                        class="form-control" 
                        id="selected_tag" 
                        name="selected_tag"
                        style="height:200px;" readonly="true">
                    </textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
            </from>
        </div>
    </div>
</div>
<!-- End Save Modal Template-->