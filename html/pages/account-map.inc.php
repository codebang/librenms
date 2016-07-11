<?php

$pagetitle[] = 'Account Topology';

?>

<h3> Account Topology </h3>
<hr>

<style type="text/css">


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

    image.buildingnode {
        width: 80px;
        height: 80px;
    }

    image.switchnode {
        width: 40px;
        height: 40px;
    }

</style>

<div id="account-topo">
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

    d3.json("account_inv.json", function(json) {
        root = json;
        root.x0 = h / 2;
        root.y0 = 0;

        function toggleAll(d) {
            if (d.children) {
                d.children.forEach(toggleAll);
//                toggle(d);
            }
        }

        // Show all nodes initially
//        root.children.forEach(toggleAll);

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

        nodeEnter.append("svg:text")
            .attr("x", function(d) {
                return d.children || d._children ? 10 : 10;
            })
            .attr("y", function(d) {
                return d.children || d._children ? 60 : 0;
            })
            .attr("dy", ".35em")
            .attr("text-anchor", function(d) { return d.children || d._children ? "middle" : "start"; })
            .text(function(d) { return d.name; })
            .style("fill-opacity", 1e-6);

        nodeEnter.append("image")
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
                    }, 500);
                }
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

</script>