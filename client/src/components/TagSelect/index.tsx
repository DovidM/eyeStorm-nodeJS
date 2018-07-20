import * as React from 'react';
import { TagQuery } from '../../graphql/tags';
import { graphql, withApollo } from 'react-apollo';

const test = false;

interface Props {
    data?: {
        loading: boolean,
        allTags: string[]
    };
    tags?: string[];
    props: Object;
}

function TagSelect(props: Props) {

    let dbTags: string[] = [];

    if (props.data!.allTags) { // later, remove `tags` from props since will get all tags from graphql
        dbTags = props.data!.allTags;
    }

    const tags = [...new Set((props.tags || []).concat(dbTags))];

    const options = tags.map((val =>
            <option key={val} value={val}>{val[0].toUpperCase() + val.slice(1)}</option>
        ));

    const select = (
        <select
            children={options}
        />
    );

    return React.cloneElement(select, props.props);
}
// export default TagSelect;

const TagSelectWithData = graphql(TagQuery)(TagSelect as any);

export default withApollo(TagSelectWithData as any) as any;
