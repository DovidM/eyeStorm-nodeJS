import * as React from 'react';
import { PrivateUserQuery, UserUpdate } from '../../../graphql/user';
import { UserDelete } from '../../../graphql/users';
import { graphql, withApollo, compose } from 'react-apollo';
import { ModifiableUserInfo } from '../shared.interfaces';
import ModifiableUserInfoComponent from './';
import { ChangeEvent } from 'react';
import graphqlErrorNotifier from '../../../helpers/graphqlErrorNotifier';

export interface Props {
    updateUser: Function;
    deleteUser: Function;
    fetchPrivateUserData: { refetch: Function };
}

export interface State {
    updates: {
        twoFactor?: boolean;
        notifications?: boolean;
    };
    delete?: boolean;
    privateUserData?: { users: [ModifiableUserInfo] };
}

/**
 * Container, has event listeners and passes data to @see ./index.tsx
 */
export class ModifiableUserInfoContainer extends React.Component<Props, State> {

    public state: State;

    constructor(props: Props) {
        super(props);

        this.onDelete = this.onDelete.bind(this);
        this.onSubmit = this.onSubmit.bind(this);
        this.onChange = this.onChange.bind(this);

        this.state = {
            updates: {}
        };
    }

    onDelete(e: ChangeEvent<HTMLInputElement>) {

        this.setState({
            delete: (e.target as HTMLInputElement).checked
        });
    }

    /**
     * Saves changes to state.updates
     *
     * @uses `e.target.name`, `e.target.value`
     */
    onChange(e: ChangeEvent<HTMLInputElement>) {

        const target = e.target as HTMLInputElement;

        const updates = Object.assign({}, this.state.updates);

        updates[target.name] = target.checked;

        this.setState({
            updates
        });
    }

    /**
     * Sends `state.updates` to server
     */
    onSubmit(target: HTMLFormElement) {

        if (this.state.delete) {
            return this.deleteUser();
        }

        if (Object.keys(this.state.updates).length > 0) {
            this.props.updateUser({
                variables: Object.assign(this.state.updates, {
                    password: (target.querySelector('[name=password]') as HTMLInputElement).value
                })
            });
        }
    }

    /**
     * Sends id of user to server so user can be deleted
     */
    deleteUser() {

        graphqlErrorNotifier(
            this.props.deleteUser,
            {
                variables: {
                    ids: [this.state.privateUserData && this.state.privateUserData.users[0].id]
                }
            },
            'userDeleted'
        );
    }

    async componentWillMount() {

        const { data } = await this.props.fetchPrivateUserData.refetch({
            profileLink: window.location.pathname.substr('/u/'.length)
        });

        if (data) {
            this.setState({
                privateUserData: data
            });
        }
    }

    render() {

        if (!this.state.privateUserData || !this.state.privateUserData.users) {
           return null;
        }

        return (
            <ModifiableUserInfoComponent
              onSubmit={this.onSubmit}
              onChange={this.onChange}
              onDelete={this.onDelete}
              {...this.state.privateUserData.users[0]}
            />
        );
    }
}

const ModifiableUserInfoContainerWithData = compose(
    graphql(PrivateUserQuery, { name: 'fetchPrivateUserData' }),
    graphql(UserUpdate, {name: 'updateUser'}),
    graphql(UserDelete, {name: 'deleteUser'})
)(ModifiableUserInfoContainer);

export default withApollo(ModifiableUserInfoContainerWithData as any) as any;
