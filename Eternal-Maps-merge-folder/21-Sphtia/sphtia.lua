Triggers = {}

function Triggers.terminal_enter(terminal, player)
  if terminal == 3 and not bobdead then
    for m in Monsters() do
      if m.valid and m.type == "green bob" then
        m.active = true
        m.life = Game.random(m.life)
      end
    end
  end
end

function Triggers.monster_killed(monster, aggressor_player, projectile)
  if monster.type == "green bob" and not bobdead then
    for p in Polygons() do
      if p.type == "must be explored" then
        p.visible_on_automap = true
        p.type = 2
      end
    end
    for p in Platforms() do
      if p.polygon.floor.z < -20 then
        p.active = true
      end
    end
    bobdead = true
  end
end
