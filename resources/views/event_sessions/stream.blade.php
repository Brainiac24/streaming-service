<script type="text/javascript" src="https://example.com/js/widget.js"></script>
<div id="example_stream_{{ $key }}"></div>
<script type="text/javascript">
    Example.init({
        session: "{{ $key }}",
        type: "stream",
        element: "example_stream_{{ $key }}"
    })
</script>
