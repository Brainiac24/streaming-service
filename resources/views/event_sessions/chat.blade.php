<script type="text/javascript" src="https://example.com/js/widget.js"></script>
<div id="example_chat_{{ $key }}"></div>
<script type="text/javascript">
    Example.init({
        session: "{{ $key }}",
        type: "chat",
        element: "example_chat_{{ $key }}"
    })
</script>
