<?php

namespace App\Policies;

use App\Models\User;
use App\Models\menu;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuPolicy
{
    use HandlesAuthorization;

    protected $out;
    public function __construct()
    {
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
        $this->out->writeln("id>>>>" . $user->id);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, menu $menu)
    {
        //
        $this->out->writeln("id>>>>" . $user->id);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
        $this->out->writeln("id>>>>" . $user->id);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, menu $menu)
    {
        //
        $this->out->writeln("id>>>>" . $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, menu $menu)
    {
        //
        $this->out->writeln("id>>>>" . $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, menu $menu)
    {
        //
        $this->out->writeln("id>>>>" . $user->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, menu $menu)
    {
        //
        $this->out->writeln("id>>>>" . $user->id);
    }
}
