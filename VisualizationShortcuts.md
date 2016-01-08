# Introduction #

You must give focus to the tracking layer in order to use the **keyboard** shortcuts.
This can be done automatically on Windows boxes, but unfortunately it does not work on Linux. So, we all must be on equal conditions ;)

On the JavaScript visualization tool this first-click-to-interact is not required. However, the JavaScript version is deprecated and may not be developed further in a future.


# Keyboard shortcuts #

By now there are defined two useful keys:

  * SPACE: pauses/plays the visualization.
  * ESC: destroys the realtime replaying and displays the data as a static picture.

# Control Panel #

If you've tried [the live demos](http://smt.speedzinemedia.com/smt/test), at this point you should know that:

  * CTRL + Click on the tracking layer (Mac users should do CMD + Click) toggles the implemented control panel.

The rest is very intuitive IMHO.
Note that on the JavaScript-only version there is no Control Panel.


# Visualization Layers #

| **layer name** | **meaning** |
|:---------------|:------------|
| background overlay | layer for separating page and tracking |
| interacted areas | areas where the mouse passed |
| mouse path     | the path followed by the mouse |
| coordinates    | tracked registration points |
| hesitations    | mouse stop zones (dwell times) |
| drag&drop/selections | clicks without releasing the button |
| clicks         | regular mouse clicks |
| direction & distances | computed mouse distances between registration points |
| active areas   | clustering of mouse coordinates |
| path centroid  | geometric center of all mouse coordinates |
| mouse pointers | entry, exit, wait and normal mouse cursors |