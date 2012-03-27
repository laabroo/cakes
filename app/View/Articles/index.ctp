<h1>List Artikel</h1>
<table>
    <tr>
        <th>title</th>
        <th>content</th>
        <th>author</th>
        <th>date</th>
    </tr>
    <?php foreach ($articles as $article) : ?>
        <tr>
            <td><?php echo $this->Html->link($article['Article']['title'], array('action' => 'view', $article['Article']['id'])); ?></td>
            <td><?php echo $article['Article']['content']; ?></td>
            <td><?php echo $article['Article']['author']; ?></td>
            <td><?php echo $article['Article']['date']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>