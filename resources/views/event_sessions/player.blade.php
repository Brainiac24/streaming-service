<script type="text/javascript" src="https://example.com/js/widget.js"></script>
<div id="example_player_{{ $key }}"></div>
<script type="text/javascript">
    Example.init({
        session: "{{ $key }}",
        type: "player",
        element: "example_player_{{ $key }}"
    })
</script>
