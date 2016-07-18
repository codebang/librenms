<?php

$pagetitle[] = 'Account Topology';

?>

<h3> Topology </h3>
<hr>

<style type="text/css">

    #account-topo svg {
        z-index: -1000;
    }

    .node circle {
        cursor: pointer;
        fill: #fff;
        stroke: steelblue;
        stroke-width: 1.5px;
    }

    .node text {
        font-size: 11px;
    }

    path.link {
        fill: none;
        stroke: #ccc;
        stroke-width: 1.5px;
    }

    image.rootnode {
        width: 80px;
        height: 80px;
    }

    image.accountnode {
        width: 80px;
        height: 80px;
    }

    image.accountnode:hover {
        cursor: pointer;
    }

    image.buildingnode {
        width: 80px;
        height: 80px;
    }

    image.buildingnode:hover {
        cursor: pointer;
    }

    image.workstationnode {
        width: 30px;
        height: 30px;
    }

    image.workstationnode:hover {
        cursor: pointer;
    }

    image.workportnode {
        width: 30px;
        height: 30px;
    }

    image.workportnode:hover {
        cursor: pointer;
    }

    image.switchnode {
        width: 40px;
        height: 40px;
    }

    image.switchnode:hover {
        cursor: pointer;
    }

    image.nodeinvisible {
        display: none;
        width: 20px;
        height: 20px;
    }

    image.nodevisible {
        display: block;
        width: 20px;
        height: 20px;
    }
    image.nodevisible:hover {
        cursor: pointer;
    }

    #port-config.inactive {
        display: none;
    }

    #port-config.active {
        z-index: 1000;
        display: block;
        position: fixed;
        box-sizing: border-box;
        width: 30%;
        color: #616161;
        font-size: 13px;
        line-height: 1.53846154;
        direction: ltr;
        -webkit-font-smoothing: antialiased;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }
    .port-widget {
        position: relative;
        margin-bottom: 20px;
        display: table;
        width: 100%;
        -webkit-border-radius: 1px;
        -moz-border-radius: 1px;
        border-radius: 1px;
        padding-left: 10px;
        padding-right: 10px;
    }

    .port-config-title {
        /*margin: 0 15px;*/
        /*padding: 10px 0;*/
        font-size: 20px;
        color: white;
        background-color: #0099cc;
        /*border-bottom: 1px solid rgba(0, 0, 0, 0.05);*/
    }

    .port-config-body {
        padding: 15px;
    }

    .port-config-body > ul {
        margin-bottom: 0;
    }

    .port-config-footer {
        padding: 10px 15px;
        border-bottom-right-radius: 1px;
        border-bottom-left-radius: 1px;
    }

</style>

<div id="account-topo">
</div>
<div id="port-config" class="inactive">
    <div class="row" style="margin-left: 0px; margin-right: 0px;">
        <div id="port-config-title" class="col-sm-12 port-config-title"><span>Port Config</span></div>
        <div class="port-widget">
            <div id="port-config-body" class="col-sm-12 port-config-body">
                <ul class="list-unstyled">
                    <div class="col-sm-6" style="padding-left: 0px;"><span>Port Allocatable</span></div>
                    <div class="col-sm-6 bootstrap-switch-container" style="margin-left: 0px;">
                        <input type="checkbox" id="port-config-status" name="port-config-status" data-size="small">
                    </div>
                </ul>
            </div>
            <div id="port-config-footer" class="col-sm-12 port-config-footer">
                <div class="col-sm-6">
                    <button id="update-port-config" class="btn btn-default btn-sm" style="float:right; margin-right: 10px;">Update</button>
                </div>
                <div class="col-sm-6">
                    <button id="close-port-config" class="btn btn-default btn-sm" style="float:left; margin-left: 10px;">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">

    var m = [20, 120, 20, 120],
        w = 1280 - m[1] - m[3],
        h = 800 - m[0] - m[2],
        i = 0,
        root;

    var tree = d3.layout.tree()
        .size([h, w]);

    var diagonal = d3.svg.diagonal()
        .projection(function(d) { return [d.y, d.x]; });

    var vis = d3.select("#account-topo").append("svg:svg")
        .attr("width", w + m[1] + m[3])
        .attr("height", h + m[0] + m[2])
        .append("svg:g")
        .attr("transform", "translate(" + m[3] + "," + m[0] + ")");

    d3.json("api/v0/topology", function(json) {
//    d3.json("account_inv.json", function(json) {
        root = json;
        root.x0 = h / 2;
        root.y0 = 0;

        function toggleAll(d) {
//            console.debug(d);
            if (d.children) {
                d.children.forEach(toggleAll);
                if (d.type && d.type == "switch")
                {
                    toggle(d);
                }
            }
        }

        // Show all nodes initially
        root.children.forEach(toggleAll);

        update(root);
    });

    function update(source) {
        var duration = d3.event && d3.event.altKey ? 5000 : 500;

        // Compute the new tree layout.
        var nodes = tree.nodes(root).reverse();

        // Normalize for fixed-depth.
        nodes.forEach(function(d) { d.y = d.depth * 180; });

        // Update the nodes…
        var node = vis.selectAll("g.node")
            .data(nodes, function(d) { return d.id || (d.id = ++i); });

        // Enter any new nodes at the parent's previous position.
        var nodeEnter = node.enter().append("svg:g")
            .attr("class", "node")
            .attr("transform", function(d) {
                return "translate(" + source.y0 + "," + source.x0 + ")";
            });

        nodeEnter.append("image")
            .attr("id", function(d) {
                if (d.type && d.type == "port")
                {
                    return "switchport-" + d.portid;
                }
            })
            .attr("xlink:href", function(d) {
                if (d.type)
                {
                    switch(d.type) {
                        case "root":
                            return "images/cloud.png";
                        case "account":
                            return "images/account.png";
                        case "building":
                            return "images/building.png";
                        case "workstation":
                            return "images/workstation.png";
                        case "port":
                        {
                            if (d.status == "disable")
                            {
                                return "images/portadmin.png";
                            }
                            else {
                                return "images/workport.png";
                            }
                        }
                    }
                }
                return "images/switch.png";
            })
            .attr("class", function(d) {
                if (d.type)
                {
                    switch(d.type) {
                        case "root":
                            return "rootnode";
                        case "account":
                            return "accountnode";
                        case "building":
                            return "buildingnode";
                        case "workstation":
                            return "workstationnode";
                        case "port":
                            return "workportnode";
                    }
                }
                return "switchnode";
            })
            .attr("x", function(d) {
                if (d.type)
                {
                    switch(d.type) {
                        case "root":
                            return -20;
                        case "account":
                            return -20;
                        case "building":
                            return -20;
                        case "workstation":
                            return -20;
                        case "port":
                            return -20;
                    }
                }
                return -40;
            })
            .attr("y", function(d) {
                if (d.type)
                {
                    switch(d.type) {
                        case "root":
                            return -40;
                        case "account":
                            return -40;
                        case "building":
                            return -40;
                        case "port":
                            return -20;
                    }
                }
                return -20;
            })
            .on("dblclick", function(d) {
                if (d3.mytimer)
                {
                    clearTimeout(d3.mytimer);
                    d3.mytimer = null;
                }
                toggle(d);
                update(d);
            })
            .on("click", function(d){
                if (d.link)
                {
                    if (d3.mytimer)
                    {
                        clearTimeout(d3.mytimer);
                    }
                    d3.mytimer = setTimeout(function() {
                        $(location).attr('href', d.link);
                        window.location = d.link;
                    }, 200);
                }
            });

        nodeEnter.append("svg:text")
            .attr("x", function(d) {
                return d.children || d._children ? 10 : 10;
            })
            .attr("y", function(d) {
                if (d.type && d.type == "root")
                {
                    return 40;
                }
                else {
                    return d.children || d._children ? 60 : 0;
                }
            })
            .attr("dy", ".35em")
            .attr("text-anchor", function(d) { return d.children || d._children ? "middle" : "start"; })
            .text(function(d) {
                if (d.used && d.total)
                {
                    return d.name + "(" + d.used + "/" + d.total + ")";
                }
                else {
                    return d.name;
                }

            })
            .style("fill-opacity", 1e-6);

        nodeEnter.append("image")
            .attr("xlink:href", function(d) {
                return "images/edit.png";
            })
            .attr("class", function(d) {
                if (d.type)
                {
                    switch(d.type) {
                        case "port":
                            return "nodevisible";
                    }
                }
                return "nodeinvisble";
            })
            .attr("x", function(d) {
                if (d.type == "port")
                {
                    return 35;
                }
            })
            .attr("y", function(d) {
                if (d.type == "port")
                {
                    return -15;
                }
            })
            .on("click", function(d){
                var portId = d.portid;
                var imageType = d3.select("#switchport-" + portId).attr("xlink:href");
                if (imageType == "images/workport.png")
                {
                    $('input[name="port-config-status"]').bootstrapSwitch('state', true, true);
                }
                else {
                    $('input[name="port-config-status"]').bootstrapSwitch('state', false, false);
                }

                $("#port-config-title span").text(d.name);
                $("#port-config-status").data("port-id", d.portid);
                var xPos = d3.event.pageX + 15;
                var yPos = d3.event.pageY - 20;
                $("#port-config")
                .css("top", yPos + 'px')
                .css("left", xPos + 'px')
                .removeClass("inactive")
                .addClass("active");
            });

        // Transition nodes to their new position.
        var nodeUpdate = node.transition()
            .duration(duration)
            .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

        nodeUpdate.select("text")
            .style("fill-opacity", 1);

        // Transition exiting nodes to the parent's new position.
        var nodeExit = node.exit().transition()
            .duration(duration)
            .attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
            .remove();


        nodeExit.select("text")
            .style("fill-opacity", 1e-6);

        // Update the links…
        var link = vis.selectAll("path.link")
            .data(tree.links(nodes), function(d) { return d.target.id; });

        // Enter any new links at the parent's previous position.
        link.enter().insert("svg:path", "g")
            .attr("class", "link")
            .attr("d", function(d) {
                var o = {x: source.x0, y: source.y0};
                return diagonal({source: o, target: o});
            })
            .transition()
            .duration(duration)
            .attr("d", diagonal);

        // Transition links to their new position.
        link.transition()
            .duration(duration)
            .attr("d", diagonal);

        // Transition exiting nodes to the parent's new position.
        link.exit().transition()
            .duration(duration)
            .attr("d", function(d) {
                var o = {x: source.x, y: source.y};
                return diagonal({source: o, target: o});
            })
            .remove();

        // Stash the old positions for transition.
        nodes.forEach(function(d) {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    }

    // Toggle children.
    function toggle(d) {
         if (d.children) {
           d._children = d.children;
           d.children = null;
         } else {
           d.children = d._children;
           d._children = null;
         }
    }

    $("#close-port-config").click(function(){
        closeConfigWindow();
    });

    $("#update-port-config").click(function(){
        var portId = $("#port-config-status").data("port-id");
        var state = $('input[name="port-config-status"]').bootstrapSwitch('state');

        if (state)
        {
            d3.select("#switchport-" + portId).attr("xlink:href", "images/workport.png");
        }
        else {
            d3.select("#switchport-" + portId).attr("xlink:href", "images/portadmin.png");
        }

        closeConfigWindow();

//        update_port_status(portId, state);
    });

    $("[name='port-config-status']").bootstrapSwitch("offColor", "danger");
    $("[name='port-config-status']").bootstrapSwitch("onColor", "success");
//    $("[name='port-config-status']").bootstrapSwitch("onText", "Yes");
//    $("[name='port-config-status']").bootstrapSwitch("offText", "No");

    $('input[name="port-config-status"]').on('switchChange.bootstrapSwitch',  function(event, state) {
//        console.debug(state);
    });

    function closeConfigWindow() {
        $("#port-config")
            .removeClass("active")
            .addClass("inactive");
    }

    function update_port_status(portId, state) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { "portid": portId, "status": state},
            dataType: 'json',
            success: function(data) {
                if (data.status == 'ok') {
                    toastr.success(data.message);
                }
                else {
                    toastr.error(data.message);
                    return;
                }

                if (state)
                {
                    d3.select("#switchport-" + portId).attr("xlink:href", "images/workport.png");
                }
                else {
                    d3.select("#switchport-" + portId).attr("xlink:href", "images/portadmin.png");
                }

                closeConfigWindow();
            },
            error: function() {
                toastr.error('Could not set this override');
            }
        });
    }


</script>
